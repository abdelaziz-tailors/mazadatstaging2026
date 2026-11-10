<?php

namespace App\Services;

use App\Models\User\User;
use Illuminate\Support\Facades\DB;

final class PushTokenOwnership
{
    public function register(int $userId, string $token): void
    {
        DB::transaction(function () use ($userId, $token) {
            $hash = $this->lockToken($token);
            // Same token lock and user-lock order as delivery, including deleted accounts.
            $users = $this->lockUsers($userId, $token);
            $user = $users->firstWhere('id', $userId);
            if (! $user || $user->trashed()) { abort(403); }
            $otherIds = $users->filter(fn ($other) => $other->id !== $userId && $other->fcm_token === $token)->modelKeys();
            User::withTrashed()->whereKey($otherIds)->update(['fcm_token' => null]);
            $user->update(['fcm_token' => $token]);
            DB::table('push_token_owners')->where('token_hash', $hash)->update(['user_id' => $userId]);
        }, 3);
    }

    /** Execute inside the notification transaction. Never return a token for use after unlocking. */
    public function deliver(int $userId, callable $send): void
    {
        $token = User::whereKey($userId)->value('fcm_token');
        if (! $token) { return; }
        $hash = $this->lockToken($token);
        $users = $this->lockUsers($userId, $token);
        $user = $users->firstWhere('id', $userId);
        $owner = DB::table('push_token_owners')->where('token_hash', $hash)->lockForUpdate()->first();
        if (! $user || $user->trashed() || ! $user->is_active || $user->fcm_token !== $token
            || $users->contains(function ($other) use ($userId, $token) {
                return $other->id !== $userId && $other->fcm_token === $token;
            }) || ($owner->user_id !== null && (int) $owner->user_id !== $userId)) {
            return;
        }
        DB::table('push_token_owners')->where('token_hash', $hash)->update(['user_id' => $userId]);
        $send($token);
    }

    private function lockUsers(int $userId, string $token)
    {
        // Registrations for this token are serialized by its registry row. Scan
        // historical tokens without locking the whole unindexed users table;
        // lock only candidate primary keys and recheck their current values.
        $ids = User::withTrashed()->where('fcm_token', $token)->pluck('id')->push($userId)->unique()->all();
        return User::withTrashed()->whereKey($ids)->orderBy('id')->lockForUpdate()->get();
    }

    private function lockToken(string $token): string
    {
        $hash = hash('sha256', $token);
        // ON DUPLICATE KEY UPDATE takes an exclusive lock immediately. An
        // INSERT IGNORE shared lock followed by FOR UPDATE can deadlock when
        // two registrations encounter the same existing token together.
        DB::table('push_token_owners')->upsert([['token_hash' => $hash, 'user_id' => null]], ['token_hash'], ['token_hash']);
        DB::table('push_token_owners')->where('token_hash', $hash)->lockForUpdate()->first();
        return $hash;
    }
}
