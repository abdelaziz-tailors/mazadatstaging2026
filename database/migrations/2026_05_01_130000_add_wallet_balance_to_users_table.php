<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'wallet_balance')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->double('wallet_balance')->default(0);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'wallet_balance')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
};
