<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('sheep_type');
            $table->string('age');
            $table->decimal('expected_price', 12, 2);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'needs edit'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('seller_submission_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_submission_id')->constrained('seller_submissions')->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_submission_media');
        Schema::dropIfExists('seller_submissions');
    }
};
