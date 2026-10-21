<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        foreach ($this->audit() as $sql) {
            DB::statement($sql);
        }
    }

    /**
     * Read-only schema audit.
     * Only prepares ALTER statements for NOT NULL email columns.
     */
    public function audit(): array
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException(
                'This migration requires MySQL/MariaDB.'
            );
        }

        $plans = [];

        foreach (['users', 'admins', 'user_otps'] as $table) {

            $column = DB::selectOne(
                'SELECT IS_NULLABLE, EXTRA
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = ?
                 AND COLUMN_NAME = ?',
                [$table, 'email']
            );

            if (!$column) {
                throw new RuntimeException(
                    "Email column not found in {$table}"
                );
            }

            // Already nullable: no change needed.
            if ($column->IS_NULLABLE === 'YES') {
                continue;
            }

            if ($column->EXTRA !== '') {
                throw new RuntimeException(
                    "Unexpected email column attributes in {$table}"
                );
            }

            $create = (array) DB::selectOne(
                "SHOW CREATE TABLE `{$table}`"
            );

            if (
                !isset($create['Create Table']) ||
                !preg_match(
                    '/^\s*`email`\s+(.+)$/m',
                    $create['Create Table'],
                    $matches
                )
            ) {
                throw new RuntimeException(
                    "Cannot inspect email definition in {$table}"
                );
            }

            $definition = rtrim(trim($matches[1]), ',');

            if (!preg_match('/\bNOT NULL\b/i', $definition)) {
                throw new RuntimeException(
                    "Unexpected email nullability in {$table}"
                );
            }

            // Preserve the original column attributes.
            $definition = preg_replace(
                '/\bNOT NULL\b/i',
                'NULL',
                $definition,
                1
            );

            $plans[] = "ALTER TABLE `{$table}` "
                . "MODIFY COLUMN `email` {$definition}";
        }

        return $plans;
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Manual reviewed rollback required.'
        );
    }
};