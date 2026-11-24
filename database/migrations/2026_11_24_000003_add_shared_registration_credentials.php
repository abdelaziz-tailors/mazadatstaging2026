<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Audit before DDL; never backfill or merge historical identities.
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'sqlite'], true)) { throw new RuntimeException('Unsupported database'); }
        if (DB::select("SELECT 1 FROM users WHERE deleted_at IS NULL AND NULLIF(TRIM(email), '') IS NOT NULL GROUP BY LOWER(TRIM(email)), user_type HAVING COUNT(*) > 1 LIMIT 1")) {
            throw new RuntimeException('Existing email/type conflicts require review; no records changed.');
        }
        $indexes = [];
        if ($driver === 'mysql') {
            foreach (DB::select('SHOW INDEX FROM users') as $index) {
                if (! $index->Non_unique && $index->Key_name !== 'PRIMARY') {
                    $indexes[$index->Key_name][(int) $index->Seq_in_index] = $index->Column_name;
                }
            }
            foreach (['users', 'admins', 'user_otps'] as $name) {
                $row = DB::selectOne('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$name]);
                if (! $row || strtoupper($row->ENGINE) !== 'INNODB') { throw new RuntimeException('Shared credentials require InnoDB; no engine conversion performed.'); }
            }
        } else {
            foreach (DB::select('PRAGMA index_list(users)') as $index) {
                if ($index->unique) {
                    $name = str_replace('`', '``', $index->name);
                    $indexes[$index->name] = array_map(fn ($c) => $c->name, DB::select("PRAGMA index_info(`{$name}`)"));
                }
            }
        }
        $drop = [];
        foreach ($indexes as $name => $columns) {
            ksort($columns);
            if (in_array(array_values($columns), [['email'], ['registration_email_key']], true)) {
                if (strpos($name, 'sqlite_autoindex') === 0) { throw new RuntimeException('Inline SQLite email constraint requires reviewed rebuild.'); }
                $drop[] = str_replace('`', '``', $name);
            }
        }
        if (Schema::hasTable('credential_groups') || Schema::hasTable('registration_locks')) {
            throw new RuntimeException('Review partial DDL and migration history before retry.');
        }
        Schema::create('credential_groups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id(); $table->string('phone')->unique();
            $table->string('email')->nullable()->unique(); $table->string('password');
        });
        Schema::create('registration_locks', function (Blueprint $table) {
            $table->engine = 'InnoDB'; $table->string('phone', 32)->primary();
        });
        foreach (['users', 'user_otps'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('credential_group_id')->nullable()->constrained('credential_groups');
            });
        }
        // Preserve generated key and all records; replace only its global uniqueness.
        $key = "CASE WHEN deleted_at IS NULL THEN NULLIF(LOWER(TRIM(email)), '') ELSE NULL END";
        DB::statement("ALTER TABLE users ADD COLUMN shared_email_key VARCHAR(255) GENERATED ALWAYS AS ({$key}) VIRTUAL");
        DB::statement('CREATE UNIQUE INDEX users_shared_email_type_unique ON users (shared_email_key, user_type)');
        foreach ($drop as $name) {
            DB::statement($driver === 'mysql' ? "ALTER TABLE users DROP INDEX `{$name}`" : "DROP INDEX `{$name}`");
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Shared identities require a reviewed forward fix; no destructive rollback.');
    }
};
