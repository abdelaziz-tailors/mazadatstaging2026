<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_otps', 'national_id')) {
            Schema::table('user_otps', function (Blueprint $table) {
                $table->string('national_id', 32)
                    ->nullable()
                    ->after('user_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_otps', 'national_id')) {
            Schema::table('user_otps', function (Blueprint $table) {
                $table->dropColumn('national_id');
            });
        }
    }
};
