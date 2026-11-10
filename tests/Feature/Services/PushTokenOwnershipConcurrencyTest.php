<?php

namespace Tests\Feature\Services;

use App\Models\{Notification, SellerSubmission};
use App\Models\User\User;
use App\Services\PushTokenOwnership;
use Illuminate\Support\Facades\DB;
use Tests\Support\NotificationTestCase;

/** Independent processes and demonstrable InnoDB lock waits, never SQLite concurrency claims. */
class PushTokenOwnershipConcurrencyTest extends NotificationTestCase
{
    private $directory;
    private $workers = [];

    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->mysqlConfig) { $this->markTestSkipped('Requires disposable MariaDB on loopback port 33328.'); }
        $this->directory = sys_get_temp_dir().'/mazadat-token-race-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel()) { DB::rollBack(); }
        foreach ($this->workers as $name => $process) {
            touch($this->directory.'/'.$name.'.go'); touch($this->directory.'/'.$name.'.release');
        }
        foreach ($this->workers as $process) { proc_close($process); }
        parent::tearDown();
    }

    private function waitFor(callable $condition, string $message): void
    {
        $deadline = microtime(true) + 15;
        do {
            if ($condition()) { $this->assertTrue(true); return; }
            usleep(20000);
        } while (microtime(true) < $deadline);
        $this->fail($message);
    }

    private function start(string $name, string $action, int $id, bool $pause = false): void
    {
        if ($pause) { touch($this->directory.'/'.$name.'.pause'); }
        $process = proc_open([PHP_BINARY, '-d', 'extension=sodium', '-d', 'extension=zip', base_path('tests/Support/push_token_worker.php'),
            getenv('NOTIFICATIONS_MYSQL_CONFIG'), $this->directory, $name, $action, (string) $id, 'shared-device'],
            [0 => ['pipe', 'r'], 1 => ['file', $this->directory.'/'.$name.'.log', 'a'],
                2 => ['file', $this->directory.'/'.$name.'.log', 'a']], $pipes);
        $this->assertIsResource($process); fclose($pipes[0]); $this->workers[$name] = $process;
        $this->waitFor(fn () => is_file($this->directory.'/'.$name.'.ready'), 'Worker must boot.');
        touch($this->directory.'/'.$name.'.go');
    }

    private function assertWaiting(string $name): void
    {
        $connectionId = (int) file_get_contents($this->directory.'/'.$name.'.ready');
        $this->waitFor(function () use ($connectionId) {
            $row = DB::selectOne('SELECT trx_state FROM information_schema.INNODB_TRX WHERE trx_mysql_thread_id = ?', [$connectionId]);
            if ($row && $row->trx_state === 'LOCK WAIT') { return true; }
            $status = (array) DB::selectOne('SHOW ENGINE INNODB STATUS');
            foreach (explode('---TRANSACTION', $status['Status']) as $transaction) {
                if ((strpos($transaction, 'MariaDB thread id '.$connectionId.',') !== false
                    || strpos($transaction, 'MySQL thread id '.$connectionId.',') !== false)
                    && strpos($transaction, 'LOCK WAIT') !== false) { return true; }
            }
            return false;
        }, 'Independent worker must demonstrably wait for the common token lock.');
    }

    private function finish(string $name): void
    {
        $this->waitFor(fn () => is_file($this->directory.'/'.$name.'.done') || is_file($this->directory.'/'.$name.'.error'), 'Worker must finish.');
        $exitCode = proc_close($this->workers[$name]);
        unset($this->workers[$name]);
        $this->assertSame(0, $exitCode, file_get_contents($this->directory.'/'.$name.'.log'));
    }

    private function receipt(User $seller): Notification
    {
        $request = SellerSubmission::create(['user_id' => $seller->id, 'partner_id' => $seller->id,
            'sheep_type' => 'Private animal', 'age' => '2', 'expected_price' => 100, 'status' => 'pending']);
        $request->update(['status' => 'approved']);
        return Notification::sole();
    }

    public function test_simultaneous_account_registrations_serialize_and_leave_one_token_owner(): void
    {
        $one = User::create(['is_active' => 1]); $two = User::create(['is_active' => 1]);
        DB::table('push_token_owners')->insert(['token_hash' => hash('sha256', 'shared-device')]);
        DB::beginTransaction();
        DB::table('push_token_owners')->lockForUpdate()->first();
        $this->start('one', 'register', $one->id); $this->start('two', 'register', $two->id);
        $this->assertWaiting('one'); $this->assertWaiting('two');
        DB::commit(); $this->finish('one'); $this->finish('two');
        $this->assertSame(1, User::where('fcm_token', 'shared-device')->count());
        $this->assertSame(User::where('fcm_token', 'shared-device')->sole()->id, (int) DB::table('push_token_owners')->value('user_id'));
    }

    public function test_account_switch_waits_for_delivery_but_in_flight_payload_contains_no_private_information(): void
    {
        $seller = User::create(['is_active' => 1, 'app_lang' => 'en']); $other = User::create(['is_active' => 1]);
        app(PushTokenOwnership::class)->register($seller->id, 'shared-device'); $receipt = $this->receipt($seller);
        $this->start('push', 'push', $receipt->id, true);
        $this->waitFor(fn () => is_file($this->directory.'/push.payload'), 'Push must reach mocked FCM while holding token lock.');
        $this->start('switch', 'register', $other->id); $this->assertWaiting('switch');
        touch($this->directory.'/push.release'); $this->finish('push'); $this->finish('switch');
        $payload = json_decode(file_get_contents($this->directory.'/push.payload'), true);
        $this->assertSame(['token' => 'shared-device', 'title' => 'Mazadat', 'body' => 'Open the app to view your notifications.',
            'data' => ['type' => 'inbox_updated', 'screen' => 'notification', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK']], $payload);
        $this->assertSame($seller->id, $receipt->fresh()->user_id);
        $this->assertNotNull($receipt->fresh()->push_completed_at);
        $this->assertNull($seller->fresh()->fcm_token); $this->assertSame('shared-device', $other->fresh()->fcm_token);
        $this->assertSame(1, Notification::count());
    }

    public function test_delivery_waiting_on_account_switch_rechecks_current_owner_and_sends_nothing(): void
    {
        $seller = User::create(['is_active' => 1, 'app_lang' => 'en']); $other = User::create(['is_active' => 1]);
        app(PushTokenOwnership::class)->register($seller->id, 'shared-device'); $receipt = $this->receipt($seller);
        $this->start('switch', 'register-hold', $other->id);
        $this->waitFor(fn () => is_file($this->directory.'/switch.holding'), 'Switch must hold its uncommitted token ownership.');
        $this->start('push', 'push', $receipt->id); $this->assertWaiting('push');
        touch($this->directory.'/switch.release'); $this->finish('switch'); $this->finish('push');
        $this->assertFileDoesNotExist($this->directory.'/push.payload');
        $this->assertNotNull($receipt->fresh()->push_completed_at);
        $this->assertSame($seller->id, $receipt->fresh()->user_id);
    }
}
