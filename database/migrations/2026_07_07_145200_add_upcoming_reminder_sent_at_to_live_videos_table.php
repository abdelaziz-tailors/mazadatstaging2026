<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->timestamp('upcoming_reminder_sent_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('live_videos', function (Blueprint $table) {
            $table->dropColumn('upcoming_reminder_sent_at');
        });
    }
};
