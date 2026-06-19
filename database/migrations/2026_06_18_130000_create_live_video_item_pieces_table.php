<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveVideoItemPiecesTable extends Migration
{
    public function up()
    {
        Schema::create('live_video_item_pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_video_item_id')->constrained('live_video_items')->cascadeOnDelete();
            $table->unsignedSmallInteger('piece_number');
            $table->string('age')->nullable();
            $table->double('weight')->nullable();
            $table->string('piece_multiplier_number')->nullable();
            $table->string('identifier')->nullable();
            $table->string('baham_count')->nullable();
            $table->timestamps();

            $table->unique(['live_video_item_id', 'piece_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('live_video_item_pieces');
    }
}
