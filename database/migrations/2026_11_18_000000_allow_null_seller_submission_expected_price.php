<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('This migration requires MySQL/MariaDB.');
        }

        // Match the existing DECIMAL precision; no stored prices are rewritten.
        DB::statement('ALTER TABLE seller_submissions MODIFY COLUMN expected_price DECIMAL(12, 2) NULL');
    }

    public function down(): void
    {
        // Restoring NOT NULL could reject listings created without a price.
        throw new RuntimeException('Manual reviewed rollback required: preserve listings with unspecified prices.');
    }
};
