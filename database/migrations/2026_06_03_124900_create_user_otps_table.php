<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('register');
            $table->boolean('is_verified')->default(false);
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('user_name');
            $table->string('user_type')->default('buyer');
            $table->string('commercial_register')->nullable();
            $table->string('password');
            $table->string('otp');
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['phone', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
