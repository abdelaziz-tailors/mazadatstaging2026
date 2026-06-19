<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            if (Schema::hasColumn('live_video_items', 'age_id')) {
                $table->dropForeign(['age_id']);
                $table->dropColumn('age_id');
            }

            $columns = ['age', 'weight', 'identifier', 'age_type','baham_count'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('live_video_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_video_items', function (Blueprint $table) {
            $table->string('age')->nullable();
            $table->double('weight')->nullable();
            $table->string('identifier')->nullable();
            $table->unsignedBigInteger('age_id')->nullable();
            $table->foreign('age_id')->references('id')->on('ages')->onDelete('cascade');
        });

        Schema::table('live_video_item_pieces', function (Blueprint $table) {
            if (Schema::hasColumn('live_video_item_pieces', 'age_type')) {
                $table->dropColumn('age_type');
            }
        });
    }
};
