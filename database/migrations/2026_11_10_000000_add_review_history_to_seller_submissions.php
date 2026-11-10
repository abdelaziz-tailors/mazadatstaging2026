<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seller_submissions', function (Blueprint $table) {
            $table->json('review_history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seller_submissions', function (Blueprint $table) {
            $table->dropColumn('review_history');
        });
    }
};
