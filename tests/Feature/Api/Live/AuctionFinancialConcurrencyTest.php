<?php

namespace Tests\Feature\Api\Live;

use App\Http\Controllers\FirebaseController;
use App\Models\{LiveVideo, LiveVideoItem, VideoComment, Order, OrderItem, WalletTransaction};
use App\Models\User\User;
use App\Services\{AuctionWalletSettlement, OrderService};
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuctionFinancialConcurrencyTest extends TestCase
{
    private $owner;
    private $buyer;
    private $other;
    private $auction;
    private $item;
    private $bid;
    private $fixtureFloors;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('FINANCIAL_CONCURRENCY_TESTS') !== '1') { $this->markTestSkipped('Isolated opt-in suite.'); }
        $this->assertGreaterThanOrEqual(80400, PHP_VERSION_ID);
        $this->assertSame('testing', app()->environment());
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('mazadat_bid_test', DB::connection()->getDatabaseName());
        $this->assertSame(0, DB::transactionLevel());
        $this->fixtureFloors = [
            'live_videos' => (int) DB::table('live_videos')->max('id'),
            'videos' => (int) DB::table('videos')->max('id'),
            'users' => (int) DB::table('users')->max('id'),
        ];
        foreach (['owner', 'buyer', 'other'] as $role) {
            $this->$role = User::create(['name' => $role, 'user_name' => 'fin_'.bin2hex(random_bytes(8)),
                'phone' => '01'.random_int(100000000,999999999), 'password' => bcrypt('test'),
                'user_type' => $role === 'owner' ? 'vendor' : 'buyer', 'gender' => 'male']);
        }
        $this->auction = LiveVideo::create(['user_id' => $this->owner->id, 'status' => 'start', 'start_price' => 1000,
            'commission_amount' => 0, 'service_fee' => 0, 'commission_payer' => 'buyer']);
        DB::table('videos')->insertOrIgnore(['id' => $this->auction->id, 'title' => 'Legacy FK fixture']);
        $this->item = LiveVideoItem::create(['live_video_id' => $this->auction->id, 'user_id' => $this->owner->id,
            'seller_id' => $this->owner->id, 'status' => 'working', 'bidding' => 100]);
        $this->bid = $this->newBid($this->item, 1100);
        Passport::actingAs($this->owner, [], 'api');
        $this->withHeaders(['x-api-key' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM', 'Accept-Language' => 'en']);
        $this->app->instance(FirebaseController::class, \Mockery::mock(FirebaseController::class)->shouldIgnoreMissing());
    }

    protected function tearDown(): void
    {
        try {
            // Only this opt-in suite's committed fixtures in its disposable DB.
            // Keep them visible to workers during the test, then avoid leaking
            // item IDs into tests that intentionally align item/auction IDs.
            if ($this->fixtureFloors !== null) {
                foreach ($this->fixtureFloors as $table => $floor) {
                    DB::table($table)->where('id', '>', $floor)->delete();
                }
            }
        } finally {
            parent::tearDown();
        }
    }
    private function newBid($item, $amount): VideoComment
    {
        return VideoComment::create(['video_id' => $item->live_video_id, 'live_video_item_id' => $item->id,
            'user_id' => $this->buyer->id, 'comment' => (string) $amount]);
    }

    private function award($bid = null)
    {
        return $this->postJson('/api/live/items/auction-award/'.$this->item->id, ['comment_id' => ($bid ?? $this->bid)->id]);
    }

    public function test_non_owner_cannot_award(): void
    {
        Passport::actingAs($this->other, [], 'api');
        $this->award()->assertJsonPath('success', false);
        $this->assertNull($this->item->fresh()->user_finished_id);
        $this->assertSame(0, OrderItem::where('live_video_item_id', $this->item->id)->count());
    }

    public function test_foreign_item_bid_and_stale_bid_cannot_win(): void
    {
        $foreign = LiveVideoItem::create(['live_video_id' => $this->auction->id, 'status' => 'working']);
        $this->award($this->newBid($foreign, 9999))->assertJsonPath('success', false);
        $this->newBid($this->item, 1200);
        $this->award()->assertJsonPath('success', false);
        $this->assertNull($this->item->fresh()->finished_price);
    }

    public function test_order_insert_failure_rolls_back_award_and_does_not_publish(): void
    {
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldNotReceive('UserAuctions');
        $this->app->instance(FirebaseController::class, $mock);
        OrderItem::creating(function () { throw new \RuntimeException('injected line failure'); });
        try { $this->award()->assertStatus(500); }
        finally { OrderItem::flushEventListeners(); }
        $this->assertNull($this->item->fresh()->user_finished_id);
        $this->assertSame(0, Order::where('live_video_id', $this->auction->id)->count());
    }

    public function test_retry_does_not_republish_or_change_paid_order(): void
    {
        $mock = \Mockery::mock(FirebaseController::class);
        $mock->shouldReceive('UserAuctions')->once();
        $this->app->instance(FirebaseController::class, $mock);
        $this->award()->assertJsonPath('success', true);
        $order = Order::where('live_video_id', $this->auction->id)->firstOrFail();
        $order->update(['payment_status' => 'paid']);
        $this->award()->assertJsonPath('success', true);
        $this->assertSame(1, Order::where('live_video_id', $this->auction->id)->count());
        $this->assertSame(1, OrderItem::where('live_video_item_id', $this->item->id)->count());
        $this->assertEquals(1100, $order->fresh()->subtotal);
    }

    private function race(array $jobs, string $table, array $ids): array
    {
        config(['database.connections.fin_barrier' => config('database.connections.mysql')]);
        $db = DB::connection('fin_barrier');
        $db->beginTransaction();
        $db->table($table)->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
        $workers=[];
        try {
            foreach ($jobs as [$action,$id,$user,$value]) {
                $file=tempnam(sys_get_temp_dir(),'financial-');
                $pipes=[];
                $process=proc_open([PHP_BINARY,'-d','error_reporting=8191',base_path('tests/Support/auction_financial_worker.php'),
                    $action,$id,$user,$value,$file],
                    [0=>['pipe','r'],1=>['file',$file.'.log','a'],2=>['file',$file.'.log','a']],$pipes);
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $workers[]=[$process,$file];
            }
            $deadline=microtime(true)+20;
            do {
                $status=(array)$db->selectOne('SHOW ENGINE INNODB STATUS');
                $waiting=0;
                foreach ($workers as [, $file]) {
                    if (!is_file($file.'.ready')) {continue;}
                    $id=(int)file_get_contents($file.'.ready');
                    $waiting+=preg_match('/LOCK WAIT[^\n]*\n(?:[^\n]*\n){0,4}(?:MariaDB|MySQL) thread id '.$id.'\b/',$status['Status']);
                }
                if($waiting>=count($jobs)){break;}
                usleep(20000);
            } while(microtime(true)<$deadline);
            $this->assertSame(count($jobs),$waiting,'All independent workers must overlap at real InnoDB locks.');
        } finally { $db->rollBack(); }
        $results=[];
        foreach($workers as [$process,$file]){
            $deadline=microtime(true)+20;
            while(proc_get_status($process)['running'] && microtime(true)<$deadline){usleep(20000);}
            if(proc_get_status($process)['running']){proc_terminate($process);$this->fail('Worker timeout.');}
            proc_close($process);
            $result=json_decode(file_get_contents($file),true);
            $this->assertIsArray($result,file_get_contents($file.'.log'));
            $this->assertArrayHasKey('success',$result,json_encode($result));
            $result['published']=is_file($file.'.broadcast');
            if($result['published']){$this->assertTrue(json_decode(file_get_contents($file.'.broadcast'),true)['committed']);}
            $results[]=$result;
            foreach(['','.ready','.log','.broadcast'] as $suffix){if(is_file($file.$suffix)){unlink($file.$suffix);}}
        }
        return $results;
    }

    public function test_simultaneous_identical_awards_are_idempotent(): void
    {
        $job=['award',$this->item->id,$this->owner->id,$this->bid->id];
        $results=$this->race([$job,$job],'live_video_items',[$this->item->id]);
        $this->assertTrue($results[0]['success'],json_encode($results));
        $this->assertTrue($results[1]['success'],json_encode($results));
        $this->assertSame(1,count(array_filter($results,fn($r)=>$r['published'])));
        $this->assertSame(1,Order::where('live_video_id',$this->auction->id)->count());
        $this->assertSame(1,OrderItem::where('live_video_item_id',$this->item->id)->count());
    }

    public function test_simultaneous_bid_and_award_cannot_accept_a_stale_winner(): void
    {
        $results=$this->race([
            ['award',$this->item->id,$this->owner->id,$this->bid->id],
            ['bid',$this->item->id,$this->other->id,1200],
        ],'live_video_items',[$this->item->id]);
        $this->assertSame(1,count(array_filter($results,fn($r)=>$r['success'])),json_encode($results));
        $item=$this->item->fresh();
        if($results[0]['success']){
            $this->assertEquals(1100,$item->finished_price);
            $this->assertEquals($this->buyer->id,$item->user_finished_id);
        }else{
            $this->assertNull($item->user_finished_id);
            $this->assertSame(2,VideoComment::where('live_video_item_id',$item->id)->count());
        }
    }

    public function test_simultaneous_item_attachments_preserve_one_order_and_total(): void
    {
        $this->item->update(['finished_price'=>1100,'user_finished_id'=>$this->buyer->id]);
        $second=LiveVideoItem::create(['live_video_id'=>$this->auction->id,'seller_id'=>$this->owner->id,
            'finished_price'=>1200,'user_finished_id'=>$this->buyer->id]);
        $results=$this->race([
            ['attach',$this->item->id,$this->owner->id,0],
            ['attach',$second->id,$this->owner->id,0],
        ],'live_videos',[$this->auction->id]);
        $this->assertTrue($results[0]['success'],json_encode($results));
        $this->assertTrue($results[1]['success'],json_encode($results));
        $orders=Order::where('live_video_id',$this->auction->id)->get();
        $this->assertCount(1,$orders);
        $this->assertSame(2,$orders[0]->items()->count());
        $this->assertEquals(2300,$orders[0]->fresh()->subtotal);
    }

    public function test_simultaneous_orders_in_different_auctions_have_distinct_numbers(): void
    {
        $this->item->update(['finished_price'=>1100,'user_finished_id'=>$this->buyer->id]);
        $auction=LiveVideo::create(['user_id'=>$this->owner->id,'status'=>'start']);
        $second=LiveVideoItem::create(['live_video_id'=>$auction->id,'seller_id'=>$this->owner->id,
            'finished_price'=>1200,'user_finished_id'=>$this->other->id]);
        $results=$this->race([
            ['attach',$this->item->id,$this->owner->id,0],
            ['attach',$second->id,$this->owner->id,0],
        ],'live_videos',[$this->auction->id,$auction->id]);
        $this->assertTrue($results[0]['success'],json_encode($results));
        $this->assertTrue($results[1]['success'],json_encode($results));
        $orders=Order::whereIn('live_video_id',[$this->auction->id,$auction->id])->get();
        $this->assertCount(2,$orders);
        $this->assertCount(2,$orders->pluck('order_number')->unique());
    }

    public function test_simultaneous_settlement_posts_each_ledger_entry_once(): void
    {
        $this->award()->assertJsonPath('success',true);
        $order=Order::where('live_video_id',$this->auction->id)->firstOrFail();
        $order->update(['payment_status'=>'paid']);
        $job=['settle',$order->id,$this->owner->id,0];
        $results=$this->race([$job,$job],'orders',[$order->id]);
        $this->assertTrue($results[0]['success'],json_encode($results));
        $this->assertTrue($results[1]['success'],json_encode($results));
        $ledger=WalletTransaction::where('order_id',$order->id)->get();
        $this->assertSame(1,$ledger->where('type','buyer_debit')->count());
        $this->assertSame(1,$ledger->where('type','seller_credit')->count());
        $this->assertEquals(1100,$this->owner->fresh()->wallet_balance);
        $this->assertEquals(0,$this->buyer->fresh()->wallet_balance);
        $this->assertNotNull($order->fresh()->settled_at);
    }

    public function test_settlement_failure_rolls_back_all_money_and_markers(): void
    {
        $this->award()->assertJsonPath('success',true);
        $order=Order::where('live_video_id',$this->auction->id)->firstOrFail();
        $order->update(['payment_status'=>'paid']);
        WalletTransaction::created(function($entry){if($entry->type==='seller_credit'){throw new \RuntimeException('ledger failure');}});
        try{
            AuctionWalletSettlement::settleOrderIfNeeded($order);
            $this->fail('Expected injected ledger failure.');
        }catch(\RuntimeException $e){$this->assertSame('ledger failure',$e->getMessage());}
        finally{WalletTransaction::flushEventListeners();}
        $this->assertSame(0,WalletTransaction::where('order_id',$order->id)->count());
        $this->assertEquals(0,$this->owner->fresh()->wallet_balance);
        $this->assertNull($order->fresh()->settled_at);
        $this->assertNull($order->items()->first()->settled_at);
    }

    public function test_scheduled_close_selects_this_items_highest_bid(): void
    {
        \Illuminate\Support\Facades\Bus::fake();
        $this->auction->update(['date_end_at'=>'2020-01-01','time_start_at'=>'10:00','time_end_at'=>'11:00']);
        DB::table('videos')->insertOrIgnore(['id'=>$this->item->id,'title'=>'Legacy unrelated video']);
        $foreign=LiveVideoItem::create(['live_video_id'=>$this->auction->id,'status'=>'working']);
        VideoComment::create(['video_id'=>$this->item->id,'live_video_item_id'=>$foreign->id,
            'user_id'=>$this->other->id,'comment'=>'9999']);
        $this->artisan('livevideoEnd:check')->assertExitCode(0);
        $this->assertEquals($this->buyer->id,$this->item->fresh()->user_finished_id);
        $this->assertEquals(1100,$this->item->fresh()->finished_price);
        $this->assertSame(1,OrderItem::where('live_video_item_id',$this->item->id)->count());
    }

    public function test_scheduled_close_failure_rolls_back_parent_and_all_item_awards(): void
    {
        \Illuminate\Support\Facades\Bus::fake();
        $this->auction->update(['date_end_at'=>'2020-01-01','time_start_at'=>'10:00','time_end_at'=>'11:00']);
        $second=LiveVideoItem::create(['live_video_id'=>$this->auction->id,'seller_id'=>$this->owner->id,'status'=>'working']);
        $this->newBid($second,1200);
        OrderItem::creating(function($line)use($second){if((int)$line->live_video_item_id===(int)$second->id){throw new \RuntimeException('scheduled line failure');}});
        $mock=\Mockery::mock(FirebaseController::class);
        $mock->shouldNotReceive('removeLive');
        $mock->shouldNotReceive('ChangeLiveItemStatus');
        $this->app->instance(FirebaseController::class,$mock);
        try{
            $this->artisan('livevideoEnd:check');
            $this->fail('Expected scheduled failure.');
        }catch(\RuntimeException $e){$this->assertSame('scheduled line failure',$e->getMessage());}
        finally{OrderItem::flushEventListeners();}
        $this->assertSame('start',$this->auction->fresh()->status);
        $this->assertSame('working',$this->item->fresh()->status);
        $this->assertNull($this->item->fresh()->user_finished_id);
        $this->assertSame(0,Order::where('live_video_id',$this->auction->id)->count());
        \Illuminate\Support\Facades\Bus::assertNothingDispatched();
    }

    public function test_award_broadcast_failure_and_outer_rollback_do_not_corrupt_state(): void
    {
        $mock=\Mockery::mock(FirebaseController::class);
        $mock->shouldReceive('UserAuctions')->once()->andThrow(new \RuntimeException('offline'));
        $this->app->instance(FirebaseController::class,$mock);
        $this->award()->assertJsonPath('success',true);
        $this->award()->assertJsonPath('success',true);
        $this->assertEquals(1100,$this->item->fresh()->finished_price);
        $this->assertSame(1,OrderItem::where('live_video_item_id',$this->item->id)->count());
        $second=LiveVideoItem::create(['live_video_id'=>$this->auction->id,'seller_id'=>$this->owner->id,'status'=>'working']);
        $bid=$this->newBid($second,1200);
        DB::beginTransaction();
        try {app(\App\Services\AuctionAwardService::class)->award($second->id,$bid->id,$this->owner->id);}
        finally {DB::rollBack();}
        $this->assertNull($second->fresh()->user_finished_id);
        $this->assertSame(0,OrderItem::where('live_video_item_id',$second->id)->count());
    }

    public function test_attachment_cannot_reassign_a_paid_line_or_add_to_settled_order(): void
    {
        $this->award()->assertJsonPath('success',true);
        $order=Order::where('live_video_id',$this->auction->id)->firstOrFail();
        $order->update(['payment_status'=>'paid','settled_at'=>now()]);
        $line=$order->items()->first();
        $this->item->update(['user_finished_id'=>$this->other->id]); // Inject inconsistent legacy state.
        try{
            OrderService::attachWonItem($this->item);
            $this->fail('An existing line cannot move buyers.');
        }catch(\DomainException $e){$this->assertStringContainsString('conflicts',$e->getMessage());}
        $this->assertSame($order->id,$line->fresh()->order_id);
        $second=LiveVideoItem::create(['live_video_id'=>$this->auction->id,'seller_id'=>$this->owner->id,
            'finished_price'=>1200,'user_finished_id'=>$this->buyer->id]);
        try{
            OrderService::attachWonItem($second);
            $this->fail('A settled order cannot receive new lines.');
        }catch(\DomainException $e){$this->assertStringContainsString('paid or settled',$e->getMessage());}
        $this->assertEquals(1100,$order->fresh()->subtotal);
        $this->assertSame(1,$order->items()->count());
    }
}
