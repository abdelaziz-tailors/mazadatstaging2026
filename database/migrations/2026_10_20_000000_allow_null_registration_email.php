<?php

use App\Helpers\MobileNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->audit() as $sql) {
            DB::statement($sql);
        }
    }

    /** Read-only preflight, usable independently with a SELECT-only connection. */
    public function audit(): array
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('This migration requires a verified MySQL/MariaDB schema.');
        }
        $plans = [];
        // Audit EVERY table before the first DDL statement. No user values are changed.
        foreach (['users', 'admins', 'user_otps'] as $table) {
            $metadata = DB::selectOne('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]);
            if (! $metadata || $metadata->ENGINE !== 'InnoDB') {
                throw new RuntimeException('Transactional InnoDB identity tables are required: '.$table);
            }
            $columns = collect(DB::select('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, EXTRA, CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$table]))->keyBy('COLUMN_NAME');
            $email = $columns->get('email');
            if (! $email || strtolower($email->COLUMN_TYPE) !== 'varchar(255)' || $email->EXTRA !== ''
                || ! $columns->has('phone') || ! $columns->has('deleted_at')) {
                throw new RuntimeException('Unexpected '.$table.' identity schema; inspect before deployment.');
            }
            $keys = ['phone' => MobileNumber::sqlKey()];
            if ($table !== 'user_otps') {
                // Phone is reserved across soft deletes; email can be reused after deletion.
                $keys['email'] = "CASE WHEN `deleted_at` IS NULL THEN NULLIF(LOWER(TRIM(`email`)), '') ELSE NULL END";
            }
            $alter = [];
            $tableIndexes = collect(DB::select('SHOW INDEX FROM `'.$table.'`'));
            foreach ($tableIndexes->where('Non_unique', 0)->groupBy('Key_name') as $name => $index) {
                if ($table !== 'user_otps' && $index->pluck('Column_name')->values()->all() === ['email']) {
                    throw new RuntimeException('Global email index '.$table.'.'.$name.' requires review against the active-only email policy.');
                }
            }
            foreach ($keys as $field => $expression) {
                $source = $columns->get($field);
                if (! $source || ! preg_match('/^[a-zA-Z0-9_]+$/D', $source->CHARACTER_SET_NAME ?? '')
                    || ! preg_match('/^[a-zA-Z0-9_]+$/D', $source->COLLATION_NAME ?? '')) {
                    throw new RuntimeException('Unexpected '.$table.'.'.$field.' character metadata.');
                }
                $group = $expression.($table === 'user_otps' ? ', `type`' : '');
                if (DB::selectOne('SELECT COUNT(*) AS oversized FROM `'.$table.'` WHERE CHAR_LENGTH('.$expression.') > 255')->oversized > 0) {
                    throw new RuntimeException('Oversized legacy '.$field.' keys on '.$table.' require review.');
                }
                $duplicate = DB::selectOne('SELECT COUNT(*) AS conflicts FROM (SELECT 1 FROM `'.$table.'` WHERE ('.$expression.') IS NOT NULL GROUP BY '.$group.' HAVING COUNT(*) > 1) AS identity_duplicates');
                if ((int) $duplicate->conflicts > 0) {
                    throw new RuntimeException($table.' has '.$duplicate->conflicts.' duplicate '.$field.' groups; resolve through an approved process before DDL.');
                }
                $column = 'registration_'.$field.'_key';
                $index = $table.'_registration_'.$field.'_unique';
                $existing = $columns->get($column);
                $indexes = $tableIndexes->where('Key_name', $index)->sortBy('Seq_in_index');
                $indexColumns = $table === 'user_otps' ? [$column, 'type'] : [$column];
                if ($existing) {
                    // MySQL DDL can commit partially. Never infer that a prior run completed.
                    throw new RuntimeException('Existing identity key on '.$table.'; verify partial DDL and migration history before retry.');
                }
                if ($indexes->isNotEmpty()) {
                    throw new RuntimeException('Unexpected existing identity index on '.$table);
                }
                $alter[] = 'ADD COLUMN `'.$column.'` VARCHAR(255) CHARACTER SET '.$source->CHARACTER_SET_NAME.' COLLATE '.$source->COLLATION_NAME.' GENERATED ALWAYS AS ('.$expression.') VIRTUAL';
                $alter[] = 'ADD UNIQUE INDEX `'.$index.'` (`'.implode('`, `', $indexColumns).'`)';
            }
            if ($email->IS_NULLABLE === 'NO') {
                $create = (array) DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
                if (! preg_match('/^\s*`email`\s+(.+?)(?:,)?$/m', $create['Create Table'], $definition)
                    || ! preg_match('/\bNOT NULL\b/i', $definition[1])) {
                    throw new RuntimeException('Cannot preserve '.$table.'.email definition safely.');
                }
                // Preserve charset, collation, default and comment from the actual definition.
                $alter[] = 'MODIFY COLUMN `email` '.preg_replace('/\bNOT NULL\b/i', 'NULL', rtrim($definition[1], ','), 1);
            }
            if ($alter) {
                $plans[] = 'ALTER TABLE `'.$table.'` '.implode(', ', $alter);
            }
        }
        return $plans;
    }

    public function down(): void
    {
        throw new RuntimeException('Manual reviewed rollback required: NULL emails and uniqueness guarantees must be preserved.');
    }
};
