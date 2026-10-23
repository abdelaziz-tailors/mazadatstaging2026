<?php

// Each invocation boots its own HTTP kernel and MySQL connection.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (PHP_VERSION_ID < 80400 || ! $app->environment('testing')
    || getenv('BID_CONCURRENCY_TESTS') !== '1'
    || config('database.connections.mysql.database') !== 'mazadat_bid_test') {
    throw new RuntimeException('Isolated PHP 8.4 / mazadat_bid_test environment required.');
}
[$script, $itemId, $userId, $amount, $output] = $argv;
$app->instance(App\Http\Controllers\FirebaseController::class, new class($output) extends App\Http\Controllers\FirebaseController {
    private $output;
    public function __construct($output) { $this->output = $output; }
    public function AddAuctions($record)
    {
        config(['database.connections.bid_observer' => config('database.connections.mysql')]);
        $visible = Illuminate\Support\Facades\DB::connection('bid_observer')->table('video_comments')->where('id', $record->id)->exists();
        file_put_contents($this->output.'.broadcast', json_encode(['id' => $record->id, 'committed' => $visible]));
    }
});
Laravel\Passport\Passport::actingAs(App\Models\User\User::findOrFail($userId), [], 'api');
$request = Illuminate\Http\Request::create('/api/video/auctions/add', 'POST', [
    'live_video_item_id' => $itemId, 'comment' => $amount,
], [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_ACCEPT_LANGUAGE' => 'en',
    'HTTP_X_API_KEY' => 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM']);
file_put_contents($output.'.ready', (string) Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);
file_put_contents($output, $response->getContent());
$kernel->terminate($request, $response);
