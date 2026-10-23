<?php
require __DIR__.'/../../vendor/autoload.php';
if (getenv('FIN_BASELINE') === '1') {
    require '/tmp/original-OrderService.php';
    require '/tmp/original-LiveVideoItemController.php';
}
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (PHP_VERSION_ID < 80400 || !$app->environment('testing') || getenv('FINANCIAL_CONCURRENCY_TESTS') !== '1'
    || config('database.connections.mysql.database') !== 'mazadat_bid_test') {
    throw new RuntimeException('Disposable financial test environment required.');
}
[$script,$action,$itemId,$userId,$value,$output] = $argv;
$app->instance(App\Http\Controllers\FirebaseController::class, new class($output) extends App\Http\Controllers\FirebaseController {
    private $output;
    public function __construct($output) { $this->output=$output; }
    public function UserAuctions($bid) {
        config(['database.connections.observer' => config('database.connections.mysql')]);
        $db=Illuminate\Support\Facades\DB::connection('observer');
        $item=$db->table('live_video_items')->where('id',$bid->live_video_item_id)->first();
        $line=$db->table('order_items')->where('live_video_item_id',$bid->live_video_item_id)->first();
        file_put_contents($this->output.'.broadcast', json_encode(['committed' => $item && $line
            && (int)$item->user_finished_id === (int)$bid->user_id && (float)$line->finished_price === (float)$bid->comment]));
    }
    public function AddAuctions($bid) {}
});
Laravel\Passport\Passport::actingAs(App\Models\User\User::findOrFail($userId), [], 'api');
file_put_contents($output.'.ready', (string) Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id);
try {
    if ($action === 'settle') {
        App\Services\AuctionWalletSettlement::settleOrderIfNeeded(App\Models\Order::findOrFail($itemId));
        $body=['success'=>true];
    } elseif ($action === 'attach') {
        App\Services\OrderService::attachWonItem(App\Models\LiveVideoItem::findOrFail($itemId));
        $body=['success'=>true];
    } else {
        $uri=$action === 'bid' ? '/api/video/auctions/add' : '/api/live/items/auction-award/'.$itemId;
        $data=$action === 'bid' ? ['live_video_item_id'=>$itemId,'comment'=>$value] : ['comment_id'=>$value];
        $request=Illuminate\Http\Request::create($uri,'POST',$data,[],[],['HTTP_ACCEPT'=>'application/json',
            'HTTP_ACCEPT_LANGUAGE'=>'en','HTTP_X_API_KEY'=>'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM']);
        $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
        $response=$kernel->handle($request);
        $body=json_decode($response->getContent(),true);
        $kernel->terminate($request,$response);
    }
} catch (Throwable $e) { $body=['success'=>false,'error'=>get_class($e).': '.$e->getMessage()]; }
file_put_contents($output,json_encode($body));