<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_access_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('role', ['buyer', 'seller']);
            $table->timestamps();
            $table->unique(['user_id', 'role']);
        });
    }

    public function down()
    {
        throw new \RuntimeException('Role access contains authorization data; rollback requires a reviewed compatibility plan.');
    }
};
