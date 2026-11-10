<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lazy registration avoids guessing the owner of historical shared tokens.
        Schema::create('push_token_owners', function (Blueprint $table) {
            $table->char('token_hash', 64)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_token_owners');
    }
};
