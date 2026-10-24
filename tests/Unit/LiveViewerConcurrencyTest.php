<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__).'/Support/live_viewer_fixture.php';

/** Opt-in, dedicated disposable MariaDB only. Independent OS processes, no live Firebase. */
class LiveViewerConcurrencyTest extends TestCase
{
    private $db;
    private $directory;
    private $workers = [];
    private $configPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configPath = getenv('MAZADAT_VIEWER_TEST_CONFIG');
        if (!$this->configPath) { $this->markTestSkipped('Requires explicitly identified disposable viewer database.'); }
        $config = json_decode(file_get_contents($this->configPath), true);
        [, $this->db] = viewerFixture($config, 1, function () { throw new \RuntimeException('Parent must not publish.'); });
        self::assertSame('mazadat_viewer_test', $this->db->getDatabaseName());
        $this->directory = sys_get_temp_dir().'/mazadat-viewer-'.bin2hex(random_bytes(6));
        mkdir($this->directory);
        foreach (['video_views', 'live_video_users', 'live_videos'] as $table) {
            $this->db->statement('DROP TABLE IF EXISTS '.$table);
        }
        $this->db->statement('CREATE TABLE live_videos (id BIGINT PRIMARY KEY) ENGINE=InnoDB');
        $this->db->statement('CREATE TABLE live_video_users (id BIGINT AUTO_INCREMENT PRIMARY KEY, live_video_id BIGINT, user_id BIGINT, `leave` INT DEFAULT 0, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB');
        $this->db->statement('CREATE TABLE video_views (id BIGINT AUTO_INCREMENT PRIMARY KEY, video_id BIGINT, user_id BIGINT, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB');
        $this->db->table('live_videos')->insert(['id' => 10]);
    }

    protected function tearDown(): void
    {
        foreach ($this->workers as $id => $process) { touch($this->directory.'/'.$id.'.release'); }
        foreach ($this->workers as $process) { proc_close($process); }
        if ($this->db) { $this->db->disconnect(); }
        \Mockery::close();
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
        \Illuminate\Support\Facades\Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    private function start(string $id, int $user, string $action, string $mode = 'normal'): void
    {
        $command = [PHP_BINARY, '-n', '-d', 'extension_dir='.ini_get('extension_dir'), '-d', 'extension=mbstring',
            '-d', 'extension=pdo_mysql', '-d', 'extension=openssl', '-d', 'error_reporting=8191',
            dirname(__DIR__).'/Support/live_viewer_worker.php', $this->configPath, $this->directory, $id, (string) $user, $action, $mode];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $this->directory.'/'.$id.'.log', 'a'],
            2 => ['file', $this->directory.'/'.$id.'.log', 'a']], $pipes);
        self::assertIsResource($process);
        fclose($pipes[0]);
        $this->workers[$id] = $process;
    }

    private function wait(string $file): void { viewerWait($this->directory.'/'.$file); }
    private function release(string $id): void { touch($this->directory.'/'.$id.'.release'); }

    private function finish(string $id): void
    {
        $exit = proc_close($this->workers[$id]);
        unset($this->workers[$id]);
        self::assertSame(0, $exit, file_get_contents($this->directory.'/'.$id.'.log'));
        self::assertFileExists($this->directory.'/'.$id.'.done');
    }

    private function waitForLockOrHistorySelect(string $id): string
    {
        $this->wait($id.'.started');
        $connection = (int) file_get_contents($this->directory.'/'.$id.'.started');
        $deadline = microtime(true) + 8;
        do {
            if (is_file($this->directory.'/'.$id.'.selected')) { return 'selected-history'; }
            $wait = $this->db->selectOne('SELECT trx_state FROM information_schema.INNODB_TRX WHERE trx_mysql_thread_id = ?', [$connection]);
            if ($wait && $wait->trx_state === 'LOCK WAIT') { return 'row-lock-wait'; }
            // MariaDB can omit a waiter from INNODB_TRX; verify its actual engine wait too.
            $status = (array) $this->db->selectOne('SHOW ENGINE INNODB STATUS');
            foreach (explode('---TRANSACTION', $status['Status']) as $transaction) {
                if (strpos($transaction, 'MariaDB thread id '.$connection.',') !== false
                    && strpos($transaction, 'LOCK WAIT') !== false) { return 'row-lock-wait'; }
            }
            if (is_file($this->directory.'/'.$id.'.done') || is_file($this->directory.'/'.$id.'.error')) { return 'finished-without-wait'; }
            usleep(20000);
        } while (microtime(true) < $deadline);
        return 'no-observed-lock-wait';
    }

    private function counts(): array
    {
        $path = $this->directory.'/publications.jsonl';
        return is_file($path) ? array_map(function ($line) { return json_decode($line, true)['count']; }, file($path, FILE_IGNORE_NEW_LINES)) : [];
    }

    public static function overlaps(): array
    {
        return [
            'different users join' => [false, 'addView', 1, 'addView', 2, [1, 2], 2, 2],
            'same user duplicate joins' => [false, 'addView', 1, 'addView', 1, [1, 1], 1, 1],
            'repeated leave' => [true, 'leaveView', 1, 'leaveView', 1, [0, 0], 0, 1],
            'leave overlaps another user join' => [true, 'leaveView', 1, 'addView', 2, [0, 1], 1, 2],
            'leave overlaps reconnect' => [true, 'leaveView', 1, 'addView', 1, [0, 1], 1, 1],
        ];
    }

    /** @dataProvider overlaps */
    public function test_overlapping_calls_serialize_database_and_synchronous_publication($seed, $first, $userA, $second, $userB, $counts, $active, $historical): void
    {
        if ($seed) {
            $this->db->table('live_video_users')->insert(['live_video_id' => 10, 'user_id' => 1, 'leave' => 0]);
            $this->db->table('video_views')->insert(['video_id' => 10, 'user_id' => 1]);
        }
        $this->start('a', $userA, $first, 'pause-publish');
        $this->wait('a.publish');
        $this->start('b', $userB, $second);
        self::assertSame('row-lock-wait', $this->waitForLockOrHistorySelect('b'));
        self::assertFileDoesNotExist($this->directory.'/b.publish');
        $this->release('a');
        $this->finish('a');
        $this->finish('b');
        self::assertSame($counts, $this->counts());
        self::assertSame($active, $this->db->table('live_video_users')->where('leave', 0)->distinct()->count('user_id'));
        self::assertSame($historical, $this->db->table('video_views')->count());
    }

    public function test_historical_and_live_joins_cannot_duplicate_unique_history(): void
    {
        $this->start('a', 1, 'history', 'pause-history');
        $this->wait('a.selected');
        $this->start('b', 1, 'addView', 'pause-history');
        $state = $this->waitForLockOrHistorySelect('b');
        // Always release both, including on the unfixed path, to collect the actual database result.
        $this->release('a');
        $this->finish('a');
        $this->release('b');
        $this->finish('b');
        self::assertSame(1, $this->db->table('video_views')->count(), 'Concurrent endpoints duplicated historical unique-user rows.');
        self::assertSame('row-lock-wait', $state);
        self::assertSame([1], $this->counts());
    }

    public function test_ambiguous_timeout_can_apply_an_old_count_after_a_newer_leave(): void
    {
        $this->start('a', 1, 'addView', 'timeout');
        $this->finish('a');
        self::assertTrue(json_decode(file_get_contents($this->directory.'/a.response'), true)['success']);
        self::assertSame(1, (int) file_get_contents($this->directory.'/a.publish'));
        $this->start('b', 1, 'leaveView');
        $this->finish('b');
        // An independent sink worker models a remote write completing after the caller timed out.
        $this->start('late-a', 1, 'delayed-write');
        $this->release('late-a');
        $this->finish('late-a');
        self::assertSame([0, 1], $this->counts());
        self::assertSame(0, $this->db->table('live_video_users')->where('leave', 0)->count());
        // This characterizes an unresolved transport boundary, not a Firebase emulator/device test.
        $this->start('retry', 1, 'leaveView');
        $this->finish('retry');
        self::assertSame([0, 1, 0], $this->counts(), 'A later retry repairs the projection once old writes have completed.');
    }

    public function test_late_old_leave_cannot_be_distinguished_from_a_genuine_leave(): void
    {
        foreach (['device-a' => 'addView', 'device-b' => 'addView', 'old-a' => 'leaveView'] as $id => $action) {
            $this->start($id, 1, $action);
            $this->finish($id);
        }
        self::assertSame([1, 1, 0], $this->counts());
        self::assertSame(1, $this->db->table('video_views')->count());
        self::assertSame(0, $this->db->table('live_video_users')->where('leave', 0)->count());
    }

    public function test_server_worker_death_after_publication_leaves_a_projection_without_committed_presence(): void
    {
        $this->start('crash', 1, 'addView', 'pause-after-publish');
        $this->wait('crash.published');
        self::assertTrue(proc_terminate($this->workers['crash']));
        $exit = proc_close($this->workers['crash']);
        unset($this->workers['crash']);
        self::assertNotSame(0, $exit);
        // Wait on the auction lock to ensure the dead connection has rolled back.
        $this->db->transaction(function () {
            $this->db->table('live_videos')->where('id', 10)->lockForUpdate()->first();
            self::assertSame(0, $this->db->table('live_video_users')->count());
            self::assertSame(0, $this->db->table('video_views')->count());
        });
        self::assertSame([1], $this->counts());
        // This is a real backend-process kill with a local sink, not mobile termination or Firebase transport.
        $this->start('retry-after-crash', 1, 'leaveView');
        $this->finish('retry-after-crash');
        self::assertSame([1, 0], $this->counts());
    }
}
