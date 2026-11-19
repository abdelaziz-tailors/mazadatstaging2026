<?php

namespace Tests\Feature\Bugfixes;

use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\NotificationTestCase;

class BuyerSellerRoleMysqlTest extends NotificationTestCase
{
    private $roleTableCreated = false;

    protected function setUp(): void
    {
        parent::setUp();
        if (! $this->mysqlConfig) { $this->markTestSkipped('Requires allow-listed disposable MariaDB configuration.'); }
        (require database_path('migrations/2026_11_18_000001_create_user_access_roles.php'))->up();
        $this->roleTableCreated = true;
    }

    protected function tearDown(): void
    {
        if ($this->roleTableCreated) { Schema::drop('user_access_roles'); }
        parent::tearDown();
    }

    public function test_additive_migration_preserves_account_and_enforces_membership_constraints(): void
    {
        $user = User::create(['name' => 'Owner', 'phone' => '+966512345678', 'user_type' => 'buyer', 'password' => bcrypt('secret123')]);
        $before = $user->fresh()->getRawOriginal();
        DB::transaction(function () use ($user) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            DB::table('user_access_roles')->insert(['user_id' => $user->id, 'role' => 'seller']);
        });
        $this->assertTrue($user->hasAccessRole('buyer'));
        $this->assertTrue($user->hasAccessRole('seller'));
        $this->assertSame(1, User::withAccessRole('seller')->count());
        $this->assertSame($before, $user->fresh()->getRawOriginal());
        $indexes = DB::select('SHOW INDEX FROM user_access_roles');
        $this->assertNotEmpty(array_filter($indexes, fn ($i) => $i->Key_name === 'user_access_roles_user_id_role_unique' && (int) $i->Non_unique === 0));
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('user_access_roles')->insert(['user_id' => $user->id, 'role' => 'seller']);
    }

    public function test_orphan_membership_is_rejected(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('user_access_roles')->insert(['user_id' => 99999, 'role' => 'seller']);
    }
    public function test_concurrent_membership_requests_are_idempotent(): void
    {
        $user = User::create(['name' => 'Owner', 'phone' => '+966512345678', 'user_type' => 'buyer']);
        $workers = [];
        for ($index = 0; $index < 2; $index++) {
            $pipes = [];
            $process = proc_open([PHP_BINARY, base_path('tests/Support/role-membership-worker.php'),
                realpath(getenv('NOTIFICATIONS_MYSQL_CONFIG')), (string) $user->id],
                [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $workers[] = [$process, $pipes];
        }
        foreach ($workers as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $output);
        }
        $this->assertSame(1, DB::table('user_access_roles')->where('user_id', $user->id)->count());
    }

}
