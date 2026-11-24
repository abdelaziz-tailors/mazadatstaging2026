<?php
// Manual isolated MariaDB migration check. Never uses configured application DB credentials.
require dirname(__DIR__, 2).'/vendor/autoload.php';
foreach (['APP_ENV'=>'testing', 'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>':memory:',
    'DATABASE_URL'=>'', 'DB_URL'=>'', 'APP_CONFIG_CACHE'=>'storage/framework/testing/accounts-config.php',
    'APP_ROUTES_CACHE'=>'storage/framework/testing/accounts-routes.php', 'LOG_CHANNEL'=>'null',
    'CACHE_DRIVER'=>'array', 'TELESCOPE_ENABLED'=>'false'] as $key=>$value) {
    putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = new PDO('mysql:host=127.0.0.1;port=33324', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$expected = trim(file_get_contents(storage_path('framework/testing/multi-account-db-path.txt')));
$actual = $pdo->query('SELECT @@datadir')->fetchColumn();
$normalize = fn ($p) => strtolower(rtrim(str_replace('\\', '/', $p), '/'));
if ($normalize($actual) !== $normalize($expected) || strpos(basename($expected), 'mazadat-account-test-') !== 0) {
    throw new RuntimeException('Refusing a database outside the generated temporary test directory.');
}
$name = 'multi_account_'.bin2hex(random_bytes(6));
$pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
config(['database.default'=>'accounts_isolated', 'database.connections.accounts_isolated'=>[
    'driver'=>'mysql', 'host'=>'127.0.0.1', 'port'=>33324, 'database'=>$name, 'username'=>'root',
    'password'=>'', 'charset'=>'utf8mb4', 'collation'=>'utf8mb4_unicode_ci', 'prefix'=>'', 'strict'=>true,
]]);
$db = Illuminate\Support\Facades\DB::connection();
$db->statement("CREATE TABLE users (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, phone VARCHAR(255), user_type ENUM('buyer','vendor','buyer_vendor') NOT NULL DEFAULT 'buyer', UNIQUE KEY users_phone_unique (phone)) ENGINE=InnoDB");
$db->statement("CREATE TABLE user_otps (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, phone VARCHAR(255), user_type VARCHAR(255) NOT NULL DEFAULT 'buyer', type VARCHAR(255) NOT NULL DEFAULT 'register', UNIQUE KEY user_otps_phone_type_unique (phone,type)) ENGINE=InnoDB");
$key = App\Helpers\MobileNumber::sqlKey('phone', 'mysql');
$db->statement("ALTER TABLE users ADD registration_phone_key VARCHAR(255) GENERATED ALWAYS AS ({$key}) VIRTUAL, ADD UNIQUE INDEX users_registration_phone_unique (registration_phone_key)");
$db->statement("ALTER TABLE user_otps ADD registration_phone_key VARCHAR(255) GENERATED ALWAYS AS ({$key}) VIRTUAL, ADD UNIQUE INDEX user_otps_registration_phone_unique (registration_phone_key,type)");
$db->table('users')->insert(['phone'=>'0512345678', 'user_type'=>'buyer']);
$db->table('user_otps')->insert(['phone'=>'+966512345678', 'user_type'=>'buyer', 'type'=>'register']);
$before = $db->table('users')->select('id','phone','user_type')->get()->toArray();
$checks = 0;
$assert = function ($value) use (&$checks) { if (!$value) { throw new RuntimeException('Isolated migration assertion failed at '.($checks+1)); } $checks++; };
$migration = require database_path('migrations/2026_11_24_000001_scope_account_phone_uniqueness_by_type.php');
$assert(count($migration->audit()) > 0);
$migration->up();
$assert($before == $db->table('users')->select('id','phone','user_type')->get()->toArray());
$assert($migration->audit() === []);
$db->table('users')->insert(['phone'=>'+966512345678', 'user_type'=>'seller']);
$assert($db->table('users')->count() === 2);
foreach (['users', 'user_otps'] as $table) {
    try { $db->table($table)->insert(['phone'=>'00966512345678','user_type'=>'buyer']); throw new RuntimeException('Duplicate accepted'); }
    catch (Illuminate\Database\QueryException $e) { $assert(App\Exceptions\Handler::identityConflict($e) === 'phone'); }
}
$db->table('user_otps')->insert(['phone'=>'00966512345678', 'user_type'=>'seller']);
$assert($db->table('user_otps')->count() === 2);
$db->statement('ALTER TABLE users DROP INDEX users_registration_phone_role_unique');
$db->table('users')->insert(['phone'=>'966512345678', 'user_type'=>'buyer']);
$before = $db->table('users')->get()->toArray();
try { $migration->audit(); throw new RuntimeException('Conflict audit failed'); }
catch (RuntimeException $e) { $assert(strpos($e->getMessage(), 'conflicts') !== false); }
$assert($before == $db->table('users')->get()->toArray());
try { $migration->down(); throw new RuntimeException('Unsafe rollback permitted'); }
catch (RuntimeException $e) { $assert(strpos($e->getMessage(), 'forward fix') !== false); }
echo "PASS: isolated MariaDB migration, {$checks} assertions. Database retained in temporary test directory.\n";
