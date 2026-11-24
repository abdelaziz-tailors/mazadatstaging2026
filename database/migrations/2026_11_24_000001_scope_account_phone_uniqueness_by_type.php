<?php

use App\Helpers\MobileNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Read-only preflight. No rows are rewritten, including soft-deleted records. */
    public function audit(): array
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'sqlite'], true)) {
            throw new RuntimeException('Unsupported multi-account migration database.');
        }
        $plans = [];
        foreach (['users', 'user_otps'] as $table) {
            foreach (['phone', 'user_type'] as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    throw new RuntimeException("Missing {$table}.{$column}");
                }
            }
            if (DB::table($table)->whereNull('user_type')->orWhere('user_type', '')->exists()) {
                throw new RuntimeException("Account types need explicit review in {$table}; no records changed.");
            }
            $key = MobileNumber::sqlKey('phone', $driver);
            $group = $table === 'users' ? 'user_type' : 'user_type, type';
            if (DB::select("SELECT 1 FROM `{$table}` WHERE phone IS NOT NULL GROUP BY {$key}, {$group} HAVING COUNT(*) > 1 LIMIT 1")) {
                throw new RuntimeException("Canonical phone/type conflicts in {$table}; no records changed.");
            }
            $indexes = [];
            if ($driver === 'mysql') {
                foreach (DB::select("SHOW INDEX FROM `{$table}`") as $index) {
                    if (! $index->Non_unique && $index->Key_name !== 'PRIMARY') {
                        $indexes[$index->Key_name][(int) $index->Seq_in_index] = $index->Column_name;
                    }
                }
            } else {
                foreach (DB::select("PRAGMA index_list(`{$table}`)") as $index) {
                    if (! $index->unique) { continue; }
                    $name = str_replace('`', '``', $index->name);
                    $indexes[$index->name] = array_map(fn ($c) => $c->name, DB::select("PRAGMA index_info(`{$name}`)"));
                }
            }
            // Establish the stronger scoped constraint BEFORE removing global constraints.
            $newName = $table.'_registration_phone_role_unique';
            if (! $this->hasColumn($table, 'multi_account_phone_key')) {
                $plans[] = "ALTER TABLE `{$table}` ADD COLUMN multi_account_phone_key VARCHAR(255) GENERATED ALWAYS AS ({$key}) VIRTUAL";
            }
            if (! isset($indexes[$newName])) {
                $columns = $table === 'users' ? 'multi_account_phone_key, user_type' : 'multi_account_phone_key, user_type, type';
                $plans[] = "CREATE UNIQUE INDEX {$newName} ON `{$table}` ({$columns})";
            }
            foreach ($indexes as $name => $columns) {
                ksort($columns);
                $columns = array_values($columns);
                if (in_array($columns, [['phone'], ['registration_phone_key'], ['phone', 'type'], ['registration_phone_key', 'type']], true)) {
                    if (strpos($name, 'sqlite_autoindex') === 0) {
                        throw new RuntimeException('Inline SQLite constraint requires an explicitly reviewed table rebuild; refused.');
                    }
                    $quoted = str_replace('`', '``', $name);
                    $plans[] = $driver === 'mysql' ? "ALTER TABLE `{$table}` DROP INDEX `{$quoted}`" : "DROP INDEX `{$quoted}`";
                }
            }
        }
        if ($driver === 'mysql') {
            $column = DB::selectOne("SHOW COLUMNS FROM users LIKE 'user_type'");
            if (strpos($column->Type, 'enum(') === 0 && strpos($column->Type, "'seller'") === false) {
                if ($column->Null !== 'NO' || $column->Default !== 'buyer') {
                    throw new RuntimeException('Unexpected user_type attributes; review before altering enum.');
                }
                $type = substr($column->Type, 0, -1).",'seller')";
                array_unshift($plans, "ALTER TABLE users MODIFY user_type {$type} NOT NULL DEFAULT 'buyer'");
            }
        }
        return $plans;
    }

    private function hasColumn(string $table, string $column): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA table_xinfo(`{$table}`)") as $field) {
                if ($field->name === $column) { return true; }
            }
            return false;
        }
        return Schema::hasColumn($table, $column);
    }

    public function up(): void
    {
        foreach ($this->audit() as $sql) { DB::statement($sql); }
    }

    public function down(): void
    {
        throw new RuntimeException('Independent accounts may share phones. Restore global uniqueness only after an approved conflict audit; use a forward fix.');
    }
};
