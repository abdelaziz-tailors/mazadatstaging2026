<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE users MODIFY COLUMN user_type ENUM('buyer','vendor','buyer_vendor','seller') NOT NULL DEFAULT 'buyer'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE users MODIFY COLUMN user_type ENUM('buyer','vendor','buyer_vendor') NOT NULL DEFAULT 'buyer'"
        );
    }
};
