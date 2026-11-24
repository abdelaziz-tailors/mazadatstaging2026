<?php
// Manual test: only the freshly generated disposable loopback server is permitted.
require dirname(__DIR__, 2).'/vendor/autoload.php';
foreach (['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:',
    'DATABASE_URL' => '', 'DB_URL' => '', 'LOG_CHANNEL' => 'null', 'CACHE_DRIVER' => 'array',
    'APP_CONFIG_CACHE' => 'storage/framework/testing/challenge-config.php',
    'APP_ROUTES_CACHE' => 'storage/framework/testing/challenge-routes.php', 'TELESCOPE_ENABLED' => 'false'] as $key => $value) {
    putenv($key.'='.$value); $_ENV[$key] = $value; $_SERVER[$key] = $value;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = new PDO('mysql:host=127.0.0.1;port=33329', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$expected = trim(file_get_contents(storage_path('framework/testing/challenge-db-path.txt')));
$normalize = fn ($path) => strtolower(rtrim(str_replace('\\', '/', $path), '/'));
if ($normalize($pdo->query('SELECT @@datadir')->fetchColumn()) !== $normalize($expected)
    || ! preg_match('/^mazadat-challenge-test-[a-f0-9]{32}$/', basename($expected))) {
    throw new RuntimeException('Refusing database outside the disposable test directory.');
}
if (($argv[1] ?? '') === '--shutdown') {
    $pdo->exec('SHUTDOWN');
    echo "Verified disposable MariaDB shut down; test data retained.\n";
    exit(0);
}
$worker = ($argv[1] ?? '') === '--worker';
$name = $worker ? ($argv[2] ?? '') : 'challenge_'.bin2hex(random_bytes(6));
if (! preg_match('/^challenge_[a-f0-9]{12}$/D', $name)) { throw new RuntimeException('Unsafe test database name'); }
if (! $worker) { $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); }
config(['database.default' => 'challenge_isolated', 'database.connections.challenge_isolated' => [
    'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 33329, 'database' => $name,
    'username' => 'root', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '', 'strict' => true, 'url' => null,
], 'app.dashboard_locale' => 'en', 'logging.default' => 'null']);
app()->setLocale('en');
$factory = Mockery::mock(Laravel\Passport\PersonalAccessTokenFactory::class);
$factory->shouldReceive('make')->andReturn(new Laravel\Passport\PersonalAccessTokenResult('isolated-test-token', new Laravel\Passport\Token()));
$app->instance(Laravel\Passport\PersonalAccessTokenFactory::class, $factory);
$db = Illuminate\Support\Facades\DB::connection();
if ($worker) {
    $requestFile = $expected.'/verification.json';
    $payload = json_decode(file_get_contents($requestFile), true, 512, JSON_THROW_ON_ERROR);
    echo "ready\n"; flush();
    $request = App\Http\Requests\api\User\Auth\VerifyAccountRequest::create('/api/user/verify-account', 'POST', $payload);
    try {
        $result = app(App\Http\Controllers\api\User\Auth\VerifyAccountController::class)($request)->getData(true);
        echo ! empty($result['success']) ? "success\n" : "rejected\n";
    } catch (Throwable $exception) {
        // Do not print exception messages, queries or their bindings.
        echo 'worker-error:'.get_class($exception).':'.$exception->getCode()."\n";
        exit(1);
    }
    exit(0);
}
$checks = 0;
$assert = function ($condition) use (&$checks) {
    if (! $condition) { throw new RuntimeException('Isolated challenge check failed at '.($checks + 1)); }
    $checks++;
};
foreach (['users', 'user_otps'] as $table) {
    Illuminate\Support\Facades\Schema::create($table, function (Illuminate\Database\Schema\Blueprint $schema) use ($table) {
        $schema->engine = 'InnoDB'; $schema->id();
        foreach (['name', 'user_name', 'email', 'phone', 'national_id', 'user_type', 'password', 'commercial_register', 'otp', 'type', 'city_id'] as $column) {
            $schema->string($column)->nullable();
        }
        $schema->string('image')->default('');
        $schema->boolean('is_verified')->default(false);
        $schema->boolean('is_active')->default(true);
        $schema->timestamp('expire_at')->nullable(); $schema->softDeletes(); $schema->timestamps();
        $schema->unique($table === 'users' ? ['phone', 'user_type'] : ['phone', 'user_type', 'type']);
    });
}
// Representative pre-release challenge survives unchanged with a nullable binding.
$db->table('user_otps')->insert(['phone' => '+966599999999', 'type' => 'register', 'otp' => '1111']);
$before = (array) $db->table('user_otps')->first();
$migration = require database_path('migrations/2026_11_24_000002_add_registration_challenge_hash_to_user_otps.php');
$migration->up();
$after = (array) $db->table('user_otps')->first();
$assert($after['registration_challenge_hash'] === null);
unset($after['registration_challenge_hash']); $assert($before === $after);
$db->table('user_otps')->insert(['phone' => '+966588888888', 'type' => 'register']);
$assert($db->table('user_otps')->whereNull('registration_challenge_hash')->count() === 2);
$db->table('user_otps')->where('id', 1)->update(['registration_challenge_hash' => str_repeat('a', 64)]);
try {
    $db->table('user_otps')->where('id', 2)->update(['registration_challenge_hash' => str_repeat('a', 64)]);
    throw new RuntimeException('Hash uniqueness not enforced');
} catch (Illuminate\Database\QueryException $e) { $assert($e->getCode() === '23000'); }
try { $migration->down(); throw new RuntimeException('Unsafe down accepted'); }
catch (RuntimeException $e) { $assert(strpos($e->getMessage(), 'forward fix') !== false); }

$issue = function ($type) {
    $request = App\Http\Requests\api\User\Auth\RegisterRequest::create('/api/user/register', 'POST', [
        'name' => 'Isolated Owner', 'user_name' => 'isolated_'.$type, 'phone' => '+966512345678',
        'password' => 'secret123', 'password_confirmation' => 'secret123', 'user_type' => $type,
    ]);
    return app(App\Http\Controllers\api\User\Auth\RegisterController::class)($request)->getData(true)['data'];
};
$buyer = $issue('buyer'); $seller = $issue('seller');
$assert($buyer['challenge_id'] !== $seller['challenge_id']);
$sellerBefore = (array) $db->table('user_otps')->where('user_type', 'seller')->first();
file_put_contents($expected.'/verification.json', json_encode(['phone' => $buyer['phone'], 'otp' => '1111', 'challenge_id' => $buyer['challenge_id']]));
$db->beginTransaction();
$db->table('user_otps')->where('registration_challenge_hash', hash('sha256', $buyer['challenge_id']))->lockForUpdate()->first();
$processes = []; $pipes = [];
try {
    for ($i = 0; $i < 2; $i++) {
        $processes[$i] = proc_open([PHP_BINARY, __FILE__, '--worker', $name],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes[$i]);
        if (! is_resource($processes[$i])) { throw new RuntimeException('Worker could not start'); }
        fclose($pipes[$i][0]); stream_set_timeout($pipes[$i][1], 15);
        $assert(trim(fgets($pipes[$i][1])) === 'ready');
    }
    $deadline = microtime(true) + 20;
    do {
        $waiting = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.innodb_trx WHERE trx_state='LOCK WAIT' AND trx_mysql_thread_id <> CONNECTION_ID()")->fetchColumn();
        $active = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE DB = '{$name}' AND COMMAND IN ('Query', 'Execute')")->fetchColumn();
        // A contender may wait in optimizer Statistics before entering InnoDB's row wait list.
        if ($waiting >= 1 && $active === 2) { break; } usleep(50000);
    } while (microtime(true) < $deadline);
    if (! ($waiting >= 1 && $active === 2)) {
        echo "Barrier counts: row waiters={$waiting}, active queries={$active}\n";
    }
    $assert($waiting >= 1 && $active === 2);
    $db->commit();
    $results = [];
    foreach ($processes as $i => $process) {
        $results[] = trim(stream_get_contents($pipes[$i][1]));
        $errors = stream_get_contents($pipes[$i][2]);
        fclose($pipes[$i][1]); fclose($pipes[$i][2]);
        $assert(proc_close($process) === 0 && $errors === '');
        unset($processes[$i]);
    }
    sort($results); $assert($results === ['rejected', 'success']);
    $assert($db->table('users')->where('user_type', 'buyer')->count() === 1);
    $assert($db->table('user_otps')->where('user_type', 'buyer')->whereNotNull('deleted_at')->whereNull('otp')->count() === 1);
    $assert($sellerBefore === (array) $db->table('user_otps')->where('user_type', 'seller')->first());
} finally {
    if ($db->transactionLevel()) { $db->rollBack(); }
    foreach ($processes as $process) { if (is_resource($process)) { proc_terminate($process); proc_close($process); } }
}
echo 'PASS: MariaDB '.$pdo->query('SELECT VERSION()')->fetchColumn().", {$checks} assertions; real controller workers: one success, one rejection; two active queries, {$waiting} observed row-lock waits.\n";
echo "Disposable test data retained; no application database credentials used.\n";
