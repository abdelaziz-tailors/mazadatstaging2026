<?php

namespace Tests\Feature\Services;

use App\Models\{LiveVideo,LiveVideoItem,Order,OrderItem,OrderItemService,WalletTransaction};
use App\Models\User\User;
use App\Services\{OrderService,PieceServiceService};
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FinancialMutationConcurrencyTest extends TestCase
{
    private $users;
    private $floors;

    protected function setUp(): void
    {
        parent::setUp();
        if(getenv('MUTATION_CONCURRENCY_TESTS')!=='1'){$this->markTestSkipped('Disposable opt-in suite.');}
        $this->assertGreaterThanOrEqual(80400,PHP_VERSION_ID);
        $this->assertSame('testing',app()->environment());
        $this->assertSame('mysql',DB::connection()->getDriverName());
        $this->assertSame('mazadat_bid_test',DB::connection()->getDatabaseName());
        $this->assertSame(0,DB::transactionLevel());
        $this->floors=['live_videos'=>(int)DB::table('live_videos')->max('id'),'users'=>(int)DB::table('users')->max('id')];
        $this->users=collect(range(1,4))->map(fn()=>User::create(['name'=>'Mutation test',
            'user_name'=>'mutation_'.bin2hex(random_bytes(8)), 'phone'=>'01'.random_int(100000000,999999999),
            'password'=>bcrypt('test'),'user_type'=>'buyer_vendor','gender'=>'male']));
        $this->withHeaders(['x-api-key'=>'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM','Accept-Language'=>'en']);
    }

    protected function tearDown(): void
    {
        try{if($this->floors!==null){foreach($this->floors as $table=>$floor){DB::table($table)->where('id','>',$floor)->delete();}}}
        finally{parent::tearDown();}
    }

    private function order(int $buyer=0,int $seller=1,bool $partner=false): Order
    {
        $auction=LiveVideo::create(['user_id'=>$this->users[2]->id,'status'=>'end','commission_amount'=>0,
            'commission_payer'=>'buyer','service_fee'=>0,'partner_id'=>$partner?$this->users[2]->id:null]);
        $item=LiveVideoItem::create(['live_video_id'=>$auction->id,'seller_id'=>$this->users[$seller]->id,
            'user_finished_id'=>$this->users[$buyer]->id,'finished_price'=>1000,'status'=>'finished']);
        return OrderService::attachWonItem($item)->fresh(['items']);
    }

    private function spawn(string $action,Order $order,int $serviceId,string $gate,bool $ledger=false): array
    {
        $file=tempnam(sys_get_temp_dir(),'mutation-');
        $pipes=[];
        $process=proc_open([PHP_BINARY,'-d','error_reporting=8191',base_path('tests/Support/financial_mutation_worker.php'),
            $action,$order->id,$order->items[0]->id,$serviceId,$file,$gate,$ledger?'1':'0'],
            [0=>['pipe','r'],1=>['file',$file.'.log','a'],2=>['file',$file.'.log','a']],$pipes);
        $this->assertIsResource($process);fclose($pipes[0]);
        return [$process,$file];
    }

    private function waitFor(callable $condition,string $message): void
    {
        $deadline=microtime(true)+20;
        do{if($condition()){return;}usleep(20000);}while(microtime(true)<$deadline);
        $this->fail($message);
    }

    private function waiting(array $worker): bool
    {
        if(!is_file($worker[1].'.ready')){return false;}
        $id=(int)file_get_contents($worker[1].'.ready');
        $status=(array)DB::selectOne('SHOW ENGINE INNODB STATUS');
        return preg_match('/LOCK WAIT[^\n]*\n(?:[^\n]*\n){0,4}(?:MariaDB|MySQL) thread id '.$id.'\b/',$status['Status'])===1;
    }

    private function finish(array $worker): array
    {
        [$process,$file]=$worker;
        $this->waitFor(fn()=>!proc_get_status($process)['running'],'Worker timeout');
        proc_close($process);
        $result=json_decode(file_get_contents($file),true);
        $this->assertIsArray($result,file_get_contents($file.'.log'));
        $result['attempts']=is_file($file.'.attempts')?strlen(file_get_contents($file.'.attempts')):0;
        foreach(['','.ready','.stage','.attempts','.log'] as $suffix){if(is_file($file.$suffix)){unlink($file.$suffix);}}
        return $result;
    }

    public static function feeModes(): array{return [['add'],['update'],['delete']];}

    /** @dataProvider feeModes */
    public function test_fee_edit_and_payment_have_one_consistent_financial_result(string $mode): void
    {
        $order=$this->order(0,1,true);
        $serviceId=0;
        if($mode!=='add'){
            $serviceId=OrderItemService::create(['order_item_id'=>$order->items[0]->id,'price'=>100,'custom_name'=>'{"en":"Fee","ar":"Fee"}'])->id;
            OrderService::recalculateTotals($order->fresh(['items']));
        }
        $gate=tempnam(sys_get_temp_dir(),'fee-gate-');
        $fee=$this->spawn('fee-'.$mode,$order,$serviceId,$gate);
        $pay=null;
        try{
            $this->waitFor(fn()=>is_file($fee[1].'.stage'),'Fee worker did not reach pre-write barrier');
            $pay=$this->spawn('pay',$order,0,$gate);
            // Original code lets payment finish while the checked fee edit is
            // paused. Fixed code makes payment wait on the fee's order lock.
            $this->waitFor(fn()=>strlen(file_get_contents($pay[1]))>0 || $this->waiting($pay),'Payment must finish or be proven blocked');
        }finally{file_put_contents($gate.'.go','go');}
        $feeResult=$this->finish($fee);$payResult=$this->finish($pay);
        unlink($gate);unlink($gate.'.go');
        $this->assertTrue($feeResult['success']??false,json_encode($feeResult));
        $this->assertTrue($payResult['success']??false,json_encode($payResult));
        $fees=(float)OrderItemService::where('order_item_id',$order->items[0]->id)->sum('price');
        $this->assertEquals($mode==='delete'?0:200,$fees);
        $this->assertEquals(1000-$fees,$this->users[1]->fresh()->wallet_balance);
        $this->assertEquals($fees,$this->users[2]->fresh()->wallet_balance);
        $this->assertEquals($fees,$order->fresh()->piece_services_total);
        $this->assertSame('paid',$order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->settled_at);
    }

    public static function settlementModes(): array{return [['pay',true],['settle',true],['pay',false]];}

    /** @dataProvider settlementModes */
    public function test_cross_order_wallet_contention_is_atomic_and_retryable(string $mode,bool $reverse): void
    {
        $one=$this->order();
        $two=$reverse?$this->order(1,0):$this->order();
        if($mode==='settle'){Order::whereIn('id',[$one->id,$two->id])->update(['payment_status'=>'paid']);}
        $gate=tempnam(sys_get_temp_dir(),'wallet-gate-');
        $a=$this->spawn($mode,$one,0,$gate,true);
        $b=$this->spawn($mode,$two,0,$gate,true);
        try{
            $this->waitFor(function()use($a,$b){
                $sa=is_file($a[1].'.stage');$sb=is_file($b[1].'.stage');
                return ($sa&&$sb)||($sa&&$this->waiting($b))||($sb&&$this->waiting($a));
            },'Both workers must hold or contend on wallet locks');
        }finally{file_put_contents($gate.'.go','go');}
        $ra=$this->finish($a);$rb=$this->finish($b);
        unlink($gate);unlink($gate.'.go');
        $this->assertTrue($ra['success'],json_encode($ra));
        $this->assertTrue($rb['success'],json_encode($rb));
        $this->assertSame(2,$ra['attempts']+$rb['attempts'],'Ordered wallet acquisition must avoid the reproduced role cycle');
        $this->assertEquals($reverse?1000:0,$this->users[0]->fresh()->wallet_balance);
        $this->assertEquals($reverse?1000:2000,$this->users[1]->fresh()->wallet_balance);
        $this->assertSame(4,WalletTransaction::whereIn('order_id',[$one->id,$two->id])->count());
        foreach([$one,$two] as $order){
            $this->assertSame('paid',$order->fresh()->payment_status);
            $this->assertNotNull($order->fresh()->settled_at);
            $again=$this->spawn($mode,$order,0,sys_get_temp_dir().'/unused-gate');
            $this->assertTrue($this->finish($again)['success']);
        }
        $this->assertSame(4,WalletTransaction::whereIn('order_id',[$one->id,$two->id])->count());
    }

    public function test_fee_authorization_and_paid_guard_are_preserved(): void
    {
        $order=$this->order();
        $line=$order->items[0];
        $service=OrderItemService::create(['order_item_id'=>$line->id,'price'=>100,'custom_name'=>'{"en":"Fee"}']);
        Passport::actingAs($this->users[3],[],'api');
        $this->postJson('/api/user/piece-services',['order_item_id'=>$line->id,'price'=>200,'custom_name'=>['en'=>'Fee','ar'=>'Fee']])->assertStatus(403);
        $this->postJson('/api/user/piece-services/'.$service->id,['price'=>200])->assertStatus(403);
        $this->deleteJson('/api/user/piece-services/'.$service->id)->assertStatus(403);
        Passport::actingAs($this->users[0],[],'api');
        $order->update(['payment_status'=>'paid']);
        $this->postJson('/api/user/piece-services',['order_item_id'=>$line->id,'price'=>200,'custom_name'=>['en'=>'Fee','ar'=>'Fee']])->assertStatus(422);
        $this->postJson('/api/user/piece-services/'.$service->id,['price'=>200])->assertStatus(422);
        $this->deleteJson('/api/user/piece-services/'.$service->id)->assertStatus(422);
        $this->assertEquals(100,$service->fresh()->price);
    }

    public function test_stale_cached_unpaid_order_cannot_add_a_fee(): void
    {
        $order=$this->order();$line=$order->items[0]->load('order');
        config(['database.connections.mutation_observer'=>config('database.connections.mysql')]);
        DB::connection('mutation_observer')->table('orders')->where('id',$order->id)->update(['payment_status'=>'paid']);
        try{PieceServiceService::addToOrderItem($line,['price'=>200,'custom_name'=>['en'=>'Fee','ar'=>'Fee']]);$this->fail('Cached unpaid status must not authorize writing');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $this->assertSame(0,$line->services()->count());
    }

    public function test_fee_write_failure_rolls_back_totals_and_service(): void
    {
        $order=$this->order();$line=$order->items[0];
        OrderItemService::created(function(){throw new \RuntimeException('fee failure');});
        try{PieceServiceService::addToOrderItem($line,['price'=>200,'custom_name'=>['en'=>'Fee','ar'=>'Fee']]);$this->fail('Expected fault');}
        catch(\RuntimeException $e){$this->assertSame('fee failure',$e->getMessage());}
        finally{OrderItemService::flushEventListeners();}
        $this->assertSame(0,$line->services()->count());
        $this->assertEquals(0,$order->fresh()->piece_services_total);
    }

    public static function retryModes(): array{return [['pay'],['settle']];}

    /** @dataProvider retryModes */
    public function test_deadlock_retry_reloads_state_and_posts_once(string $mode): void
    {
        $order=$this->order();
        if($mode==='settle'){$order->update(['payment_status'=>'paid']);}
        $injected=false;
        WalletTransaction::created(function($entry)use(&$injected){
            if(!$injected && $entry->type==='seller_credit'){
                $injected=true;
                throw new \PDOException('Deadlock found when trying to get lock; try restarting transaction',40001);
            }
        });
        try{
            if($mode==='pay'){
                \Illuminate\Support\Facades\Auth::guard('admin')->setUser(\App\Models\Admin::where('type','!=','partner')->firstOrFail());
                app(\App\Http\Controllers\Dashboard\OrderController::class)->update(
                    \Illuminate\Http\Request::create('/admin/orders/'.$order->id,'POST',['payment_status'=>'paid','status'=>'confirmed']),$order->id);
            }else{\App\Services\AuctionWalletSettlement::settleOrderIfNeeded($order);}
        }finally{WalletTransaction::flushEventListeners();}
        $this->assertTrue($injected);
        $this->assertSame('paid',$order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->settled_at);
        $this->assertEquals(1000,$this->users[1]->fresh()->wallet_balance);
        $this->assertSame(2,WalletTransaction::where('order_id',$order->id)->count());
    }
}
