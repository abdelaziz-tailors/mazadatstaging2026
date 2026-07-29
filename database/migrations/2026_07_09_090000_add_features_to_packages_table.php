<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Same shape/pattern as the existing name/description columns:
            // a JSON-encoded {"ar": [...], "en": [...]} map of feature bullets.
            $table->text('features')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
