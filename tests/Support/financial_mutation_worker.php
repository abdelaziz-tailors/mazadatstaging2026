<?php
require __DIR__.'/../../vendor/autoload.php';
if (getenv('MUTATION_BASELINE') === '1') {
    require '/tmp/before-PieceServiceService.php';
    require '/tmp/before-AuctionWalletSettlement.php';
    require '/tmp/before-OrderController.php';
}
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(PHP_VERSION_ID<80400 || !$app->environment('testing') || getenv('MUTATION_CONCURRENCY_TESTS')!=='1'
    || config('database.connections.mysql.database')!=='mazadat_bid_test'){throw new RuntimeException('Disposable test environment required.');}
[$script,$action,$orderId,$lineId,$serviceId,$output,$gate,$hookLedger]=$argv;
function waitForGate($gate) {
    $deadline=microtime(true)+20;
    while(!is_file($gate.'.go') && microtime(true)<$deadline){usleep(10000);}
    if(!is_file($gate.'.go')){throw new RuntimeException('Gate timeout');}
}
if(str_starts_with($action,'fee-')){
    $event=['fee-add'=>'creating','fee-update'=>'updating','fee-delete'=>'deleting'][$action];
    App\Models\OrderItemService::$event(function()use($output,$gate){
        file_put_contents($output.'.stage','fee pre-write');
        waitForGate($gate);
    });
}
if($hookLedger==='1'){
    App\Models\WalletTransaction::created(function($entry)use($output,$gate){
        if($entry->type==='buyer_debit'){
            file_put_contents($output.'.attempts','1',FILE_APPEND);
            file_put_contents($output.'.stage','buyer wallet locked');
            waitForGate($gate);
        }
    });
}
file_put_contents($output.'.ready',(string)Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id);
try {
    if($action==='pay'){
        Illuminate\Support\Facades\Auth::guard('admin')->setUser(App\Models\Admin::where('type','!=','partner')->firstOrFail());
        app(App\Http\Controllers\Dashboard\OrderController::class)->update(
            Illuminate\Http\Request::create('/admin/orders/'.$orderId,'POST',['payment_status'=>'paid','status'=>'confirmed']),$orderId);
        $body=['success'=>true];
    }elseif($action==='settle'){
        App\Services\AuctionWalletSettlement::settleOrderIfNeeded(App\Models\Order::findOrFail($orderId));
        $body=['success'=>true];
    }else{
        $order=App\Models\Order::findOrFail($orderId);
        Laravel\Passport\Passport::actingAs(App\Models\User\User::findOrFail($order->buyer_id),[],'api');
        $uri='/api/user/piece-services'.($action==='fee-add'?'':'/'.$serviceId);
        $data=$action==='fee-add'?['order_item_id'=>$lineId,'price'=>200,'custom_name'=>['en'=>'Fee','ar'=>'Fee']]:['price'=>200];
        $request=Illuminate\Http\Request::create($uri,$action==='fee-delete'?'DELETE':'POST',$data,[],[],
            ['HTTP_ACCEPT'=>'application/json','HTTP_ACCEPT_LANGUAGE'=>'en',
            'HTTP_X_API_KEY'=>'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM']);
        $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
        $response=$kernel->handle($request);
        $body=json_decode($response->getContent(),true);
        $body['status']=$response->getStatusCode();
        $kernel->terminate($request,$response);
    }
}catch(Throwable $e){$body=['success'=>false,'error'=>get_class($e).': '.$e->getMessage()];}
file_put_contents($output,json_encode($body));