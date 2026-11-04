<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_video_item_pieces', function (Blueprint $table) {
            // Preserve historical weights; they cannot be converted into genders.
            $table->string('gender')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('live_video_item_pieces', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
