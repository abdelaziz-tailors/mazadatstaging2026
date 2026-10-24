<?php
// Standalone fixture only: no application bootstrap, .env, migrations or network Firebase.
use App\Http\Controllers\FirebaseController;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;

function viewerFixture(array $config, int $userId, callable $publish): array
{
    if (($config['database'] ?? '') !== 'mazadat_viewer_test'
        || ($config['host'] ?? '') !== '127.0.0.1'
        || empty($config['expected_hostname']) || empty($config['port'])) {
        throw new RuntimeException('Only the explicitly identified disposable viewer database is allowed.');
    }
    $app = new Application(dirname(__DIR__, 2));
    $app->instance('config', new Repository(['app' => ['locale' => 'en']]));
    $app->instance('events', new Dispatcher($app));
    $capsule = new Manager($app);
    $capsule->addConnection(array_merge($config, ['driver' => 'mysql', 'charset' => 'utf8mb4', 'prefix' => '']));
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    $db = $capsule->getConnection();
    if ($db->selectOne('SELECT @@hostname AS hostname')->hostname !== $config['expected_hostname']) {
        throw new RuntimeException('Wrong server: refusing fixture writes.');
    }
    $db->statement('SET SESSION innodb_lock_wait_timeout=10');
    $app->instance('db', $capsule->getDatabaseManager());
    Facade::clearResolvedInstances();
    Facade::setFacadeApplication($app);
    $auth = Mockery::mock();
    $auth->shouldReceive('guard')->with('api')->andReturnSelf();
    $auth->shouldReceive('user')->andReturn((object) ['id' => $userId]);
    $app->instance('auth', $auth);
    $response = Mockery::mock(\Illuminate\Contracts\Routing\ResponseFactory::class);
    $response->shouldReceive('json')->andReturnUsing(function ($data, $status = 200) {
        return new JsonResponse($data, $status);
    });
    $app->instance(\Illuminate\Contracts\Routing\ResponseFactory::class, $response);
    $locale = Mockery::mock();
    $locale->shouldReceive('getSupportedLocales')->andReturn(['en' => []]);
    \Mcamara\LaravelLocalization\Facades\LaravelLocalization::swap($locale);
    $translator = Mockery::mock(\Illuminate\Contracts\Translation\Translator::class);
    $translator->shouldReceive('get')->andReturnUsing(function ($key) { return $key; });
    $app->instance('translator', $translator);
    $logger = Mockery::mock();
    $logger->shouldReceive('warning')->andReturnNull();
    $app->instance('log', $logger);
    $firebase = Mockery::mock(FirebaseController::class);
    $firebase->shouldReceive('LiveCount')->andReturnUsing($publish);
    $app->instance(FirebaseController::class, $firebase);
    return [$app, $db];
}

function viewerWait(string $path): void
{
    $deadline = microtime(true) + 15;
    while (!is_file($path)) {
        if (microtime(true) > $deadline) { throw new RuntimeException('Barrier timed out: '.basename($path)); }
        usleep(10000);
        clearstatcache(true, $path);
    }
}

// Models a synchronous absolute-count Firebase write; the log is never a live service.
function viewerPublish(string $directory, string $worker, int $count): void
{
    $file = fopen($directory.'/publications.jsonl', 'ab');
    flock($file, LOCK_EX);
    fwrite($file, json_encode(['worker' => $worker, 'count' => $count])."\n");
    fflush($file);
    flock($file, LOCK_UN);
    fclose($file);
}
