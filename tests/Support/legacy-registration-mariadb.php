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
$pdo = new PDO('mysql:host=127.0.0.1;port=33331', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$expected = trim(file_get_contents(storage_path('framework/testing/legacy-db-path.txt')));
$normalize = fn ($path) => strtolower(rtrim(str_replace('\\', '/', $path), '/'));
if ($normalize($pdo->query('SELECT @@datadir')->fetchColumn()) !== $normalize($expected)
    || ! preg_match('/^mazadat-legacy-test-[a-f0-9]{32}$/', basename($expected))) {
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
    'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 33331, 'database' => $name,
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
    $registration = ! empty($payload['_register']); unset($payload['_register']);
    $requestClass = $registration ? App\Http\Requests\api\User\Auth\RegisterRequest::class : App\Http\Requests\api\User\Auth\VerifyAccountRequest::class;
    $controllerClass = $registration ? App\Http\Controllers\api\User\Auth\RegisterController::class : App\Http\Controllers\api\User\Auth\VerifyAccountController::class;
    $request = $requestClass::create('/api/user/test', 'POST', $payload);
    try {
        $result = app($controllerClass)($request)->getData(true);
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
    if (! $condition) { throw new RuntimeException('Isolated legacy registration check failed at '.($checks + 1)); }
    $checks++;
};
foreach (['users', 'user_otps', 'admins'] as $table) {
    Illuminate\Support\Facades\Schema::create($table, function (Illuminate\Database\Schema\Blueprint $s) use ($table) {
        $s->engine = 'InnoDB'; $s->id();
        foreach (['name', 'user_name', 'email', 'phone', 'national_id', 'user_type', 'password', 'commercial_register', 'otp', 'password_otp', 'type', 'city_id', 'user_id', 'admin_id'] as $field) { $s->string($field)->nullable(); }
        $s->string('image')->default(''); $s->boolean('is_verified')->default(false); $s->boolean('is_active')->default(true);
        $s->timestamp('expire_at')->nullable(); $s->softDeletes(); $s->timestamps();
        $s->unique($table === 'user_otps' ? ['phone', 'type'] : ['phone'], $table.'_phone_unique');
        if ($table === 'users') { $s->unique('email', 'users_email_unique'); }
    });
}
$db->statement("ALTER TABLE users ADD registration_email_key VARCHAR(255) GENERATED ALWAYS AS (CASE WHEN deleted_at IS NULL THEN NULLIF(LOWER(TRIM(email)), '') ELSE NULL END) VIRTUAL, ADD UNIQUE INDEX users_registration_email_unique (registration_email_key)");
$db->table('users')->insert(['phone' => '+966599999999', 'user_type' => 'buyer', 'email' => 'historical@example.com']);
$before = (array) $db->table('users')->first();
foreach (['000001_scope_account_phone_uniqueness_by_type', '000002_add_registration_challenge_hash_to_user_otps', '000003_add_shared_registration_credentials'] as $migration) {
    (require database_path('migrations/2026_11_24_'.$migration.'.php'))->up();
}
$after = (array) $db->table('users')->first();
foreach ($before as $key => $value) { $assert($after[$key] === $value); }
$assert($after['credential_group_id'] === null);
$payload = ['name' => 'Concurrent Owner', 'user_name' => 'concurrent_buyer', 'phone' => '+966512345678',
    'email' => 'concurrent@example.com', 'password' => 'secret123', 'password_confirmation' => 'secret123', 'user_type' => 'buyer'];
$race = function (array $payload) use ($db, $pdo, $expected, $name, $assert) {
    file_put_contents($expected.'/verification.json', json_encode($payload));
    $db->beginTransaction();
    app(App\Services\RegistrationIdentity::class)->lock($payload['phone']);
    $processes = []; $pipes = [];
    try {
        for ($i = 0; $i < 2; $i++) {
            $processes[$i] = proc_open([PHP_BINARY, __FILE__, '--worker', $name], [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']], $pipes[$i]);
            if (! is_resource($processes[$i])) { throw new RuntimeException('Worker start failed'); }
            fclose($pipes[$i][0]); stream_set_timeout($pipes[$i][1], 20);
            $line = trim(fgets($pipes[$i][1])); if ($line !== 'ready') { echo 'Worker startup: '.$line.PHP_EOL; } $assert($line === 'ready');
        }
        $deadline = microtime(true) + 15;
        do {
            $waiting = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.innodb_trx WHERE trx_state='LOCK WAIT'")->fetchColumn();
            $active = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE DB = '{$name}' AND COMMAND IN ('Query', 'Execute')")->fetchColumn();
            if ($waiting >= 1 && $active === 2) { break; } usleep(50000);
        } while (microtime(true) < $deadline);
        echo "Observed waiters: {$waiting}; active queries: {$active}".PHP_EOL; $assert($waiting >= 1 && $active === 2);
        $db->commit();
        $results = [];
        foreach ($processes as $i => $process) {
            $results[] = trim(stream_get_contents($pipes[$i][1])); $errors = stream_get_contents($pipes[$i][2]);
            fclose($pipes[$i][1]); fclose($pipes[$i][2]);
            $assert(proc_close($process) === 0 && $errors === ''); unset($processes[$i]);
        }
        sort($results); return $results;
    } finally {
        if ($db->transactionLevel()) { $db->rollBack(); }
        foreach ($processes as $process) { if (is_resource($process)) { proc_terminate($process); proc_close($process); } }
    }
};
echo "Registration race at assertion {$checks}".PHP_EOL; $assert($race(array_merge($payload, ['_register' => true])) === ['success', 'success']);
$assert($db->table('user_otps')->whereNull('deleted_at')->count() === 1);
$assert($race(['phone' => $payload['phone'], 'otp' => '1111']) === ['rejected', 'success']);
$buyer = App\Models\User\User::where('phone', $payload['phone'])->firstOrFail();
$assert($buyer->user_type === 'buyer');
$payload['user_type'] = 'seller'; $payload['user_name'] = 'concurrent_seller';
echo "Registration race at assertion {$checks}".PHP_EOL; $assert($race(array_merge($payload, ['_register' => true])) === ['success', 'success']);
$assert($db->table('user_otps')->whereNull('deleted_at')->count() === 1);
$assert($race(['phone' => $payload['phone'], 'otp' => '1111']) === ['rejected', 'success']);
$seller = App\Models\User\User::where('user_type', 'seller')->firstOrFail();
$assert($seller->id !== $buyer->id && $seller->credential_group_id === $buyer->credential_group_id);
$assert($db->table('users')->where('phone', $payload['phone'])->count() === 2);
$buyer->update(['password' => bcrypt('changed123'), 'email' => 'changed@example.com']);
$assert(Illuminate\Support\Facades\Hash::check('changed123', $seller->fresh()->password));
$assert($seller->fresh()->email === 'changed@example.com');
$assert($db->table('users')->where('id', $before['id'])->value('credential_group_id') === null);
try {
    $db->table('users')->insert(['phone' => '+966588888888', 'email' => 'changed@example.com', 'user_type' => 'buyer']);
    throw new RuntimeException('Scoped email index failed');
} catch (Illuminate\Database\QueryException $exception) { $assert(App\Exceptions\Handler::identityConflict($exception) === 'email'); }
try { (require database_path('migrations/2026_11_24_000003_add_shared_registration_credentials.php'))->down(); throw new RuntimeException('Unsafe rollback'); }
catch (RuntimeException $exception) { $assert(strpos($exception->getMessage(), 'forward fix') !== false); }
echo 'PASS: MariaDB '.$pdo->query('SELECT VERSION()')->fetchColumn().", {$checks} assertions; four races, two active contenders and observed row-lock waits per race; separate accounts and shared credentials.\n";
echo "Disposable test data retained; no application database credentials used.\n";
