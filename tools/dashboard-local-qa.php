<?php
/** Local-only QA bootstrap. Existing database reads; no application mutations. */
declare(strict_types=1);
if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) exit(1);
if (PHP_SAPI === 'cli-server') {
    $token = getenv('MAZADAT_LOCAL_QA_TOKEN');
    if (!$token || !hash_equals($token, $_SERVER['HTTP_X_MAZADAT_QA'] ?? '') || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true)) { http_response_code(403); exit; }
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','HEAD'], true) && !($_SERVER['REQUEST_METHOD'] === 'POST' && preg_match('#/admin/[a-z-]+/getData(?:/[0-9]+)?$#', $path))) { http_response_code(405); exit('Read-only QA: action blocked.'); }
    if (preg_match('#/(logout|delete|destroy|approve|reject)(/|$)#i', $path)) { http_response_code(405); exit('Read-only QA: action blocked.'); }
    $public = realpath(dirname(__DIR__).'/public');
    $asset = realpath($public.'/'.ltrim($path, '/'));
    if ($asset && str_starts_with($asset, $public.DIRECTORY_SEPARATOR) && is_file($asset) && preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|woff2?|ttf|eot|map)$/i', $asset)) return false;
}
$qaDirectory = sys_get_temp_dir().'/mazadat-real-dashboard-qa';
foreach (['', '/views', '/sessions'] as $suffix) if (!is_dir($qaDirectory.$suffix)) mkdir($qaDirectory.$suffix, 0700, true);
foreach ([
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_CONFIG_CACHE' => $qaDirectory.'/unused-config.php',
    'APP_ROUTES_CACHE' => $qaDirectory.'/unused-routes.php',
    'VIEW_COMPILED_PATH' => $qaDirectory.'/views',
    'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'file',
    'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
    'LOG_CHANNEL' => 'stderr', 'TELESCOPE_ENABLED' => 'false',
] as $key => $value) { putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value; }
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
if (PHP_SAPI === 'cli-server') {
    $request = Illuminate\Http\Request::capture();
    $app->instance('request', $request);
    $app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
} else {
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
}
set_exception_handler(function (Throwable $error) { fwrite(STDERR, get_class($error).': local QA failed; inspect private log.\n'); exit(1); });
config(['session.files' => $qaDirectory.'/sessions', 'session.cookie' => 'mazadat_local_qa', 'session.secure' => false, 'session.domain' => null, 'app.url' => 'http://127.0.0.1:8097', 'app.asset_url' => null]);
$connectionName = config('database.default');
$db = config('database.connections.'.$connectionName);
if ($connectionName !== 'mysql' || !in_array($db['host'] ?? null, ['localhost','127.0.0.1','::1'], true) || !empty($db['url']) || !empty($db['read']) || !empty($db['write'])) throw new RuntimeException('Only a single local MySQL connection is allowed.');
$connection = Illuminate\Support\Facades\DB::connection();
$connection->beforeExecuting(function ($sql) {
    if (!preg_match('/^\s*select\b/i', $sql) || preg_match('/\b(into\s+outfile|into\s+dumpfile|for\s+update|lock\s+in\s+share)\b/i', $sql)) throw new RuntimeException('Non-read query blocked by local QA.');
});
// The database itself enforces read-only transactions in addition to the SQL gate.
$connection->getPdo()->exec('SET SESSION TRANSACTION READ ONLY');
if (PHP_SAPI === 'cli') {
    $counts = $connection->table('admins')->whereNull('deleted_at')->selectRaw('type, count(*) as count')->groupBy('type')->get();
    echo json_encode(['local_database' => true, 'read_only' => true, 'account_types' => $counts], JSON_PRETTY_PRINT)."\n";
    if (in_array('--prepare', $argv, true)) {
        foreach (['admin','partner'] as $type) {
            $account = App\Models\Admin::where('type', $type)->first();
            if (!$account) continue;
            $session = $app['session']->driver();
            $session->start();
            $session->put(Illuminate\Support\Facades\Auth::guard('admin')->getName(), $account->getAuthIdentifier());
            $session->save();
            $encrypter = $app['encrypter'];
            $value = $encrypter->encrypt(Illuminate\Cookie\CookieValuePrefix::create('mazadat_local_qa', $encrypter->getKey()).$session->getId(), false);
            file_put_contents($qaDirectory.'/'.$type.'-state.json', json_encode(['cookies' => [['name'=>'mazadat_local_qa','value'=>$value,'domain'=>'127.0.0.1','path'=>'/','httpOnly'=>true,'secure'=>false,'sameSite'=>'Lax']], 'origins'=>[]]));
        }
        echo "Prepared temporary sessions for available account types.\n";
    }
} else {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request = Illuminate\Http\Request::capture());
    $response->send();
    $kernel->terminate($request, $response);
}
