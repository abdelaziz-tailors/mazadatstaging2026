<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $table = DB::selectOne('SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [DB::getDatabaseName(), 'user_otps']);
            if (! $table || strcasecmp($table->engine, 'InnoDB') !== 0) {
                throw new RuntimeException('Registration challenge binding requires an InnoDB user_otps table; no engine conversion was performed.');
            }
        }
        // Nullable preserves pre-release challenges without assigning them usable tokens.
        Schema::table('user_otps', function (Blueprint $table) {
            $table->char('registration_challenge_hash', 64)->nullable()
                ->unique('user_otps_registration_challenge_hash_unique');
        });
    }

    public function down(): void
    {
        // Removing binding while clients hold challenges is not a safe rollback.
        throw new RuntimeException('Registration challenge binding requires a reviewed forward fix; automatic rollback is unsafe.');
    }
};
