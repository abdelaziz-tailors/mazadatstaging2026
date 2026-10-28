<?php

namespace Tests\Feature\Services;

use App\Models\{LiveVideo, Notification, Order, SellerSubmission};
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\NotificationTestCase;

class NotificationConcurrencyTest extends NotificationTestCase
{
    public static function actions(): array
    {
        return [['auction'], ['seller'], ['order'], ['reminder'], ['push']];
    }

    /** @dataProvider actions */
    public function test_two_processes_wait_for_the_same_row_and_produce_one_event(string $kind): void
    {
        if (! $this->mysqlConfig) { $this->markTestSkipped('Requires disposable MariaDB on loopback port 33328.'); }
        $user = User::create(['is_active' => 1, 'app_lang' => 'en', 'fcm_token' => 'test-device']);
        $auction = LiveVideo::create(['user_id' => $user->id, 'status' => 'pending',
            'date_start_at' => now()->addMinutes(30)->toDateString(), 'time_start_at' => now()->addMinutes(30)->toTimeString()]);
        $entity = $auction; $action = 'status'; $status = 'start';
        if ($kind === 'seller') {
            $entity = SellerSubmission::create(['user_id' => $user->id, 'partner_id' => $user->id,
                'sheep_type' => 'Sheep', 'age' => '1', 'expected_price' => 100, 'status' => 'pending']);
            $status = 'approved';
        } elseif ($kind === 'order') {
            $entity = Order::create(['buyer_id' => $user->id, 'live_video_id' => $auction->id, 'status' => 'pending']);
            $status = 'shipping';
        } elseif ($kind === 'reminder') {
            $action = 'reminder';
        } elseif ($kind === 'push') {
            $auction->update(['status' => 'start']);
            $entity = Notification::first();
            $action = 'push';
        }
        $directory = sys_get_temp_dir().'/mazadat-notification-race-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $processes = [];
        DB::beginTransaction();
        $entity->newQuery()->whereKey($entity->id)->lockForUpdate()->firstOrFail();
        try {
            foreach (['one', 'two'] as $worker) {
                $processes[$worker] = proc_open([PHP_BINARY, base_path('tests/Support/notification_worker.php'),
                    getenv('NOTIFICATIONS_MYSQL_CONFIG'), $directory, $worker, $action, get_class($entity), (string) $entity->id, $status],
                    [0 => ['pipe', 'r'], 1 => ['file', $directory.'/'.$worker.'.log', 'a'],
                        2 => ['file', $directory.'/'.$worker.'.log', 'a']], $pipes);
                $this->assertIsResource($processes[$worker]);
                fclose($pipes[0]);
            }
            $this->waitFor(function () use ($directory) {
                return is_file($directory.'/one.ready') && is_file($directory.'/two.ready');
            }, 'Both workers must load their original models.');
            touch($directory.'/go');
            foreach (['one', 'two'] as $worker) {
                $connectionId = (int) file_get_contents($directory.'/'.$worker.'.ready');
                $this->waitFor(function () use ($connectionId) {
                    $waiting = DB::selectOne('SELECT trx_state FROM information_schema.INNODB_TRX WHERE trx_mysql_thread_id = ?', [$connectionId]);
                    if ($waiting && $waiting->trx_state === 'LOCK WAIT') { return true; }
                    $status = (array) DB::selectOne('SHOW ENGINE INNODB STATUS');
                    foreach (explode('---TRANSACTION', $status['Status']) as $transaction) {
                        if ((strpos($transaction, 'MariaDB thread id '.$connectionId.',') !== false
                                || strpos($transaction, 'MySQL thread id '.$connectionId.',') !== false)
                            && strpos($transaction, 'LOCK WAIT') !== false) { return true; }
                    }
                    return false;
                }, 'Each independent process must demonstrably wait on the row lock.');
            }
            DB::commit();
            foreach ($processes as $worker => $process) {
                $this->waitFor(function () use ($directory, $worker) {
                    return is_file($directory.'/'.$worker.'.done') || is_file($directory.'/'.$worker.'.error');
                }, 'Worker must complete.');
                $this->assertSame(0, proc_close($process), file_get_contents($directory.'/'.$worker.'.log'));
                unset($processes[$worker]);
            }
            $this->assertSame(1, Notification::count());
            if ($action === 'push') {
                $this->assertSame("sent\n", file_get_contents($directory.'/deliveries'));
                $this->assertNotNull($entity->fresh()->push_completed_at);
            }
        } finally {
            if (DB::transactionLevel()) { DB::rollBack(); }
            touch($directory.'/go');
            foreach ($processes as $process) { proc_close($process); }
        }
    }

    private function waitFor(callable $condition, string $message): void
    {
        $deadline = microtime(true) + 10;
        do {
            if ($condition()) { $this->assertTrue(true); return; }
            usleep(20000);
        } while (microtime(true) < $deadline);
        $this->fail($message);
    }
}
