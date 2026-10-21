<?php

namespace Tests\Feature\Api\User\Auth;

use App\Helpers\MobileNumber;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Opt-in: creates and drops only a randomly named disposable LOCAL database. */
class RegistrationIdentityMysqlTest extends TestCase
{
    private $database;
    private $created = false;
    private $migration;

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('MAZADAT_RUN_MYSQL_IDENTITY_TESTS') !== '1') {
            $this->markTestSkipped('Enable explicitly against a local disposable MySQL database.');
        }
        $connection = config('database.connections.mysql');
        if (! in_array($connection['host'], ['localhost', '127.0.0.1'], true)) {
            $this->markTestSkipped('Refusing a remote database connection.');
        }
        // Never inherit a URL override that could redirect this local-only test.
        $connection['url'] = null;
        $connection['database'] = null;
        config(['database.connections.registration_admin' => $connection]);
        $this->database = 'mazadat_registration_test_'.bin2hex(random_bytes(8));
        DB::connection('registration_admin')->statement('CREATE DATABASE `'.$this->database.'`');
        $this->created = true;
        $connection['database'] = $this->database;
        config(['database.connections.registration_test' => $connection, 'database.default' => 'registration_test']);
        DB::purge('registration_test');
        foreach (['users', 'admins', 'user_otps'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('phone')->nullable();
                $table->string('email')->charset('utf8mb4')->collation('utf8mb4_bin')->default('')->comment('Preserve identity metadata');
                $table->string('type')->default('register');
                $table->softDeletes();
            });
        }
        $this->migration = require database_path('migrations/2026_10_20_000000_allow_null_registration_email.php');
    }

    protected function tearDown(): void
    {
        if ($this->created) {
            DB::purge('registration_test');
            DB::connection('registration_admin')->statement('DROP DATABASE `'.$this->database.'`');
            DB::purge('registration_admin');
        }
        parent::tearDown();
    }

    private function emailMetadata(): array
    {
        return (array) DB::selectOne("SELECT COLUMN_TYPE, COLLATION_NAME, COLUMN_DEFAULT, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'email'");
    }

    public function test_preflight_only_returns_plans_without_ddl(): void
    {
        $plans = $this->migration->audit();
        $this->assertCount(3, $plans);
        foreach (['users', 'admins', 'user_otps'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'registration_phone_key'));
        }
    }

    public function test_migration_preserves_attributes_and_enforces_soft_delete_policy(): void
    {
        $before = $this->emailMetadata();
        DB::table('users')->insert(['phone' => '+966512345678', 'email' => 'Original@Example.com']);
        $this->migration->up();
        $this->assertSame($before, $this->emailMetadata());
        $this->assertSame('Original@Example.com', DB::table('users')->value('email'));
        $this->assertSame('+966512345678', DB::table('users')->value('phone'));
        DB::table('users')->insert(['phone' => '+966599999999', 'email' => null]);
        DB::table('users')->insert(['phone' => '+966588888888', 'email' => null]);
        DB::table('users')->where('phone', '+966512345678')->update(['deleted_at' => now()]);
        DB::table('users')->insert(['phone' => '+966577777777', 'email' => 'original@example.com']);
        try {
            DB::table('users')->insert(['phone' => '0512345678', 'email' => null]);
            $this->fail('Soft-deleted phones remain reserved');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('phone', \App\Exceptions\Handler::identityConflict($e));
        }
        try {
            DB::table('users')->insert(['phone' => '+966566666666', 'email' => ' ORIGINAL@example.com ']);
            $this->fail('Active email must be unique');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertSame('email', \App\Exceptions\Handler::identityConflict($e));
        }
    }

    public function test_existing_identity_keys_require_review_before_retry(): void
    {
        $this->migration->up();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('verify partial DDL and migration history');
        $this->migration->up();
    }

    public function test_duplicate_audit_aborts_before_any_ddl(): void
    {
        DB::table('admins')->insert([
            ['phone' => '+966512345678', 'email' => 'one@example.com'],
            ['phone' => '0512345678', 'email' => 'two@example.com'],
        ]);
        try {
            $this->migration->up();
            $this->fail('Existing duplicates must block deployment');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('duplicate phone groups', $e->getMessage());
        }
        foreach (['users', 'admins', 'user_otps'] as $table) {
            $this->assertFalse(Schema::hasColumn($table, 'registration_phone_key'));
        }
        $this->assertSame(2, DB::table('admins')->count());
    }

    /** @dataProvider equivalentPhones */
    public function test_generated_keys_match_application_normalization(string $phone): void
    {
        $this->migration->up();
        DB::table('users')->insert(['phone' => $phone, 'email' => null]);
        $this->assertSame(MobileNumber::normalize($phone), DB::table('users')->value('registration_phone_key'));
    }

    public static function equivalentPhones(): array
    {
        return [['+966 (51) 234-5678'], ['0512345678'], ['512345678'], ['966512345678'],
            ['00966512345678'], ['01012345678'], ['+20 (10) 1234-5678'], ['00201012345678'], ["+966\t51\n234\r5678"]];
    }

    /** @dataProvider concurrentIdentities */
    public function test_two_simultaneous_connections_cannot_register_the_same_identity(string $field): void
    {
        $this->migration->up();
        $config = config('database.connections.registration_test');
        $config['race_sql'] = $field === 'phone'
            ? "INSERT INTO users (phone, email) VALUES ('0512345678', NULL)"
            : "INSERT INTO users (phone, email) VALUES ('+966599999999', ' RACE@example.com ')";
        $script = tempnam(sys_get_temp_dir(), 'mazadat-race-');
        file_put_contents($script, <<<'PHP'
<?php
$config = json_decode(stream_get_contents(STDIN), true);
try {
    $pdo = new PDO('mysql:host='.$config['host'].';port='.$config['port'].';dbname='.$config['database'], $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('SET SESSION innodb_lock_wait_timeout = 5');
    echo "ready\n"; flush();
    $pdo->exec($config['race_sql']);
    echo 'unexpected-success';
} catch (PDOException $e) { echo 'mysql-error:'.($e->errorInfo[1] ?? 0); }
PHP
        );
        $process = null;
        try {
            DB::beginTransaction();
            DB::table('users')->insert(['phone' => '+966512345678', 'email' => $field === 'email' ? 'race@example.com' : null]);
            $process = proc_open([PHP_BINARY, $script], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            fwrite($pipes[0], json_encode($config)); fclose($pipes[0]);
            stream_set_timeout($pipes[1], 10);
            $this->assertSame("ready\n", fgets($pipes[1]));
            // The competing INSERT waits on the first connection's uncommitted unique key.
            usleep(150000);
            $this->assertTrue(proc_get_status($process)['running']);
            DB::commit();
            $this->assertSame('mysql-error:1062', stream_get_contents($pipes[1]));
            fclose($pipes[1]); fclose($pipes[2]);
            proc_close($process); $process = null;
            $this->assertSame(1, DB::table('users')->count());
        } finally {
            if (DB::transactionLevel() > 0) { DB::rollBack(); }
            if (is_resource($process)) { proc_terminate($process); proc_close($process); }
            unlink($script);
        }
    }

    public static function concurrentIdentities(): array
    {
        return [['phone'], ['email']];
    }

    public function test_duplicate_email_audit_aborts_before_ddl(): void
    {
        DB::table('users')->insert([
            ['phone' => '+966512345678', 'email' => 'Used@Example.com'],
            ['phone' => '+966599999999', 'email' => ' used@example.com '],
        ]);
        try {
            $this->migration->up();
            $this->fail('Duplicate active emails must be reviewed');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('duplicate email groups', $e->getMessage());
        }
        $this->assertFalse(Schema::hasColumn('users', 'registration_phone_key'));
        $this->assertSame(2, DB::table('users')->count());
    }

    public function test_existing_global_email_index_requires_policy_review(): void
    {
        Schema::table('users', function (Blueprint $table) { $table->unique('email'); });
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('requires review against the active-only email policy');
        $this->migration->up();
    }
}
