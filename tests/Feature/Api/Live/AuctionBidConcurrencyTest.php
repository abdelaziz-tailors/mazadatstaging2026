<?php

namespace Tests\Feature\Api\Live;

use App\Http\Controllers\FirebaseController;
use App\Models\LiveVideo;
use App\Models\LiveVideoItem;
use App\Models\User\User;
use App\Models\VideoComment;
use App\Services\AuctionBidService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuctionBidConcurrencyTest extends TestCase
{
    private $item;
    private $auction;
    private $users;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('BID_CONCURRENCY_TESTS') !== '1') {
            $this->markTestSkipped('Opt-in isolated MySQL concurrency suite.');
        }
        $this->assertGreaterThanOrEqual(80400, PHP_VERSION_ID);
        $this->assertSame('testing', app()->environment());
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('mazadat_bid_test', DB::connection()->getDatabaseName());
        $this->assertSame(0, DB::transactionLevel(), 'Fixtures must be committed and visible to workers.');
        $this->users = collect([1, 2])->map(fn () => User::create([
            'name' => 'Bid test', 'user_name' => 'bid_'.bin2hex(random_bytes(8)),
            'email' => bin2hex(random_bytes(8)).'@example.test', 'phone' => '01'.random_int(100000000, 999999999),
            'password' => bcrypt('test'), 'user_type' => 'buyer', 'gender' => 'male',
        ]));
        $this->auction = LiveVideo::create(['status' => 'start', 'start_price' => 1000]);
        // Legacy migrations still reference videos for video_comments.video_id,
        // while the live API stores a live_videos ID. Satisfy that FK in fixtures.
        DB::table('videos')->insertOrIgnore(['id' => $this->auction->id, 'title' => 'Legacy FK fixture']);
        $this->item = LiveVideoItem::create(['live_video_id' => $this->auction->id, 'status' => 'working', 'bidding' => 100]);
        $this->app->instance(FirebaseController::class, \Mockery::mock(FirebaseController::class)->shouldIgnoreMissing());
    }

    private function race(array $amounts): array
    {
        config(['database.connections.bid_barrier' => config('database.connections.mysql')]);
        $barrier = DB::connection('bid_barrier');
        $barrier->beginTransaction();
        $barrier->table('live_video_items')->where('id', $this->item->id)->lockForUpdate()->first();
        $workers = [];
        try {
            foreach ($amounts as $index => $amount) {
                $file = tempnam(sys_get_temp_dir(), 'bid-race-');
                $pipes = [];
                $process = proc_open([PHP_BINARY, '-d', 'error_reporting=8191', base_path('tests/Support/auction_bid_worker.php'),
                    $this->item->id, $this->users[$index]->id, $amount, $file],
                    [0 => ['pipe', 'r'], 1 => ['file', $file.'.log', 'a'], 2 => ['file', $file.'.log', 'a']], $pipes);
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $workers[] = [$process, $file];
            }
            // Do not release until BOTH independent requests are blocked in InnoDB.
            $deadline = microtime(true) + 20;
            do {
                // MariaDB 12 may omit waiters from INNODB_LOCK_WAITS. The
                // engine monitor exposes actual waits on both MySQL/MariaDB.
                $status = (array) $barrier->selectOne('SHOW ENGINE INNODB STATUS');
                $waiting = 0;
                foreach ($workers as [, $file]) {
                    if (! is_file($file.'.ready')) { continue; }
                    $id = (int) file_get_contents($file.'.ready');
                    $waiting += preg_match('/LOCK WAIT[^\n]*\n(?:[^\n]*\n){0,4}(?:MariaDB|MySQL) thread id '.$id.'\b/', $status['Status']);
                }
                if ($waiting >= 2) { break; }
                usleep(20000);
            } while (microtime(true) < $deadline);
            $this->assertGreaterThanOrEqual(2, $waiting, 'Both HTTP workers must overlap at the held item lock.');
        } finally {
            $barrier->rollBack();
        }
        $results = [];
        foreach ($workers as [$process, $file]) {
            $deadline = microtime(true) + 20;
            while (proc_get_status($process)['running'] && microtime(true) < $deadline) { usleep(20000); }
            if (proc_get_status($process)['running']) { proc_terminate($process); $this->fail('Bid worker timed out.'); }
            proc_close($process);
            $body = json_decode(file_get_contents($file), true);
            $this->assertIsArray($body, file_get_contents($file.'.log'));
            $broadcast = is_file($file.'.broadcast') ? json_decode(file_get_contents($file.'.broadcast'), true) : null;
            $this->assertSame($body['success'], $broadcast !== null);
            if ($broadcast) { $this->assertTrue($broadcast['committed']); }
            $results[] = $body;
            foreach (['', '.ready', '.log', '.broadcast'] as $suffix) { if (is_file($file.$suffix)) { unlink($file.$suffix); } }
        }
        return $results;
    }

    public function test_equal_simultaneous_bids_accept_exactly_one_and_retry_is_stale(): void
    {
        $results = $this->race(['1100', '1100']);
        $this->assertCount(1, array_filter($results, fn ($r) => $r['success']));
        $rejected = array_values(array_filter($results, fn ($r) => ! $r['success']))[0];
        $this->assertSame(['current_price' => '1100.00', 'next_minimum' => '1200.00'], $rejected['data']);
        $this->assertSame(1, VideoComment::where('live_video_item_id', $this->item->id)->count());
        $this->rejectBid('1100', '1100.00');
        $this->assertNull($this->item->fresh()->finished_price);
        $this->assertNull($this->item->fresh()->user_finished_id);
    }

    public function test_different_simultaneous_amounts_never_lower_the_highest_bid(): void
    {
        $results = $this->race(['1100', '1200']);
        $this->assertTrue($results[1]['success']);
        $this->assertSame($results[0]['success'] ? 2 : 1, VideoComment::where('live_video_item_id', $this->item->id)->count());
        $this->rejectBid('1200', '1200.00');
    }

    private function rejectBid($amount, string $current): void
    {
        $count = VideoComment::where('live_video_item_id', $this->item->id)->count();
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldNotReceive('AddAuctions');
        $this->app->instance(FirebaseController::class, $mock);
        try {
            app(AuctionBidService::class)->place($this->item->id, $this->users[0]->id, $amount);
            $this->fail('Bid should be rejected.');
        } catch (HttpResponseException $e) {
            $this->assertSame($current, $e->getResponse()->getData(true)['data']['current_price']);
        }
        $this->assertSame($count, VideoComment::where('live_video_item_id', $this->item->id)->count());
    }

    public function test_invalid_closed_and_subincrement_bids_do_not_write_or_publish(): void
    {
        foreach (['1000', '1099.99', '-1', 'NaN', '1e4', [], '1100.001'] as $amount) { $this->rejectBid($amount, '1000.00'); }
        $this->item->update(['status' => 'finished']);
        $this->rejectBid('1100', '1000.00');
        $this->item->update(['status' => 'working']);
        $this->auction->update(['status' => 'end']);
        $this->rejectBid('1100', '1000.00');
    }

    public function test_outer_rollback_discards_bid_and_after_commit_broadcast(): void
    {
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldNotReceive('AddAuctions');
        $this->app->instance(FirebaseController::class, $mock);
        DB::beginTransaction();
        app(AuctionBidService::class)->place($this->item->id, $this->users[0]->id, '1100');
        DB::rollBack();
        $this->assertSame(0, VideoComment::where('live_video_item_id', $this->item->id)->count());
        $this->assertNull($this->item->fresh()->finished_price);
    }

    public function test_historical_maximum_excludes_replies_and_preserves_history(): void
    {
        $attributes = ['video_id' => $this->auction->id, 'live_video_item_id' => $this->item->id, 'user_id' => $this->users[0]->id];
        $highest = VideoComment::create($attributes + ['comment' => '1500']);
        VideoComment::create($attributes + ['comment' => '1200']);
        VideoComment::create($attributes + ['comment' => '9999', 'comment_id' => $highest->id]);
        VideoComment::create($attributes + ['comment' => 'legacy text']);
        $this->rejectBid('1500', '1500.00');
        $this->app->instance(FirebaseController::class, \Mockery::mock(FirebaseController::class)->shouldIgnoreMissing());
        $record = app(AuctionBidService::class)->place($this->item->id, $this->users[1]->id, '1600');
        $this->assertSame('1600.00', $record->comment);
        $this->assertSame('1500', $highest->fresh()->comment);
        $this->assertSame(5, VideoComment::where('live_video_item_id', $this->item->id)->count());
    }

    public function test_unauthenticated_service_call_and_awarded_item_are_rejected(): void
    {
        try {
            app(AuctionBidService::class)->place($this->item->id, 0, '1100');
            $this->fail('Unauthenticated bid must fail.');
        } catch (HttpResponseException $e) {
            $this->assertFalse($e->getResponse()->getData(true)['success']);
        }
        $this->item->update(['finished_price' => 1000, 'user_finished_id' => $this->users[0]->id]);
        $this->rejectBid('1100', '1000.00');
        $this->assertEquals(1000, $this->item->fresh()->finished_price);
    }

    public function test_fractional_increment_and_failed_broadcast_keep_committed_bid(): void
    {
        $this->item->update(['bidding' => '0.10']);
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldReceive('AddAuctions')->once()->andThrow(new \RuntimeException('offline'));
        $this->app->instance(FirebaseController::class, $mock);
        $record = app(AuctionBidService::class)->place($this->item->id, $this->users[0]->id, '1000.10');
        $this->assertSame('1000.10', $record->fresh()->comment);
        $this->rejectBid('1000.10', '1000.10');
    }

    public function test_deadlock_retry_inserts_and_publishes_only_once(): void
    {
        $attempts = 0;
        VideoComment::created(function ($record) use (&$attempts) {
            if ((int) $record->live_video_item_id === (int) $this->item->id && ++$attempts === 1) {
                throw new \PDOException('Deadlock found when trying to get lock; try restarting transaction', 40001);
            }
        });
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldReceive('AddAuctions')->once();
        $this->app->instance(FirebaseController::class, $mock);
        try {
            app(AuctionBidService::class)->place($this->item->id, $this->users[0]->id, '1100');
            $this->assertSame(2, $attempts);
            $this->assertSame(1, VideoComment::where('live_video_item_id', $this->item->id)->count());
        } finally {
            VideoComment::flushEventListeners();
        }
    }

    public function test_insert_failure_rolls_back_without_publication(): void
    {
        VideoComment::created(function () { throw new \RuntimeException('Injected persistence failure'); });
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldNotReceive('AddAuctions');
        $this->app->instance(FirebaseController::class, $mock);
        try {
            app(AuctionBidService::class)->place($this->item->id, $this->users[0]->id, '1100');
            $this->fail('Expected injected failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Injected persistence failure', $e->getMessage());
        } finally {
            VideoComment::flushEventListeners();
        }
        $this->assertSame(0, VideoComment::where('live_video_item_id', $this->item->id)->count());
    }
}
