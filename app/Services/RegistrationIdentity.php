<?php

namespace App\Services;

use App\Helpers\MobileNumber;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationIdentity
{
    /** A stable row serializes registration and consumption even for a first account. */
    public function lock(string $phone): void
    {
        $phone = MobileNumber::normalize($phone);
        DB::table('registration_locks')->upsert([['phone' => $phone]], ['phone'], ['phone']);
        DB::table('registration_locks')->where('phone', $phone)->lockForUpdate()->first();
    }

    public function email($email): ?string
    {
        return $email === null || trim($email) === '' ? null : mb_strtolower(trim($email), 'UTF-8');
    }

    /** Never infer a relationship between multiple ungrouped historical accounts. */
    public function resolve(string $phone, $email, string $password): ?int
    {
        $email = $this->email($email);
        $users = User::withTrashed()->where(function ($query) use ($phone, $email) {
            MobileNumber::matching($query, $phone);
            if ($email !== null) { $query->orWhere(function ($q) use ($email) { $q->whereNull('deleted_at')->whereRaw('LOWER(TRIM(email)) = ?', [$email]); }); }
        })->orderBy('id')->lockForUpdate()->get();
        if ($users->isEmpty()) { return null; }
        $groups = $users->pluck('credential_group_id')->unique();
        if ($users->count() > 1 && ($groups->count() !== 1 || $groups->first() === null)) {
            $this->conflict();
        }
        foreach ($users as $user) {
            if ($user->trashed() || ! $user->is_verified || ! $user->is_active
                || MobileNumber::normalize($user->phone) !== MobileNumber::normalize($phone)
                || $this->email($user->email) !== $email || ! Hash::check($password, $user->password)) {
                $this->conflict();
            }
        }
        $user = $users->first();
        if ($user->credential_group_id) { return (int) $user->credential_group_id; }
        $id = DB::table('credential_groups')->insertGetId([
            'phone' => MobileNumber::normalize($phone), 'email' => $email,
            'password' => $user->password,
        ]);
        // Association only: preserve all existing account fields and timestamps.
        DB::table('users')->where('id', $user->id)->update(['credential_group_id' => $id]);
        return $id;
    }

    public function conflict(): void
    {
        throw ValidationException::withMessages(['email' => 'Conflicting account credentials.']);
    }

    /** Called inside User::save's transaction for every model-based credential edit. */
    public function synchronize(User $user): void
    {
        $changes = array_intersect_key($user->getDirty(), array_flip(['phone', 'email', 'password']));
        if (! $user->exists || ! $changes) { return; }
        $current = DB::table('users')->where('id', $user->id)->first();
        $groupId = $current->credential_group_id ?? null;
        if ($groupId) {
            DB::table('credential_groups')->where('id', $groupId)->lockForUpdate()->first();
        }
        // A sibling may have associated this account since the model was loaded.
        $current = DB::table('users')->where('id', $user->id)->lockForUpdate()->first();
        $groupId = $current->credential_group_id ?? null;
        if (! $groupId) { return; }
        $user->credential_group_id = $groupId;
        $group = DB::table('credential_groups')->where('id', $groupId)->lockForUpdate()->first();
        if (! $group) { $this->conflict(); }
        if (array_key_exists('phone', $changes)) { $changes['phone'] = MobileNumber::normalize($changes['phone']); }
        if (array_key_exists('email', $changes)) { $changes['email'] = $this->email($changes['email']); }
        $phone = array_key_exists('phone', $changes) ? $changes['phone'] : $group->phone;
        $email = array_key_exists('email', $changes) ? $changes['email'] : $group->email;
        if (! MobileNumber::valid($phone) || ($email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL))) { $this->conflict(); }
        $outsiders = User::withTrashed()->where(function ($q) use ($user) {
            $q->whereNull('credential_group_id')->orWhere('credential_group_id', '<>', $user->credential_group_id);
        })->where(function ($q) use ($phone, $email) {
            MobileNumber::matching($q, $phone);
            if ($email !== null) { $q->orWhereRaw('LOWER(TRIM(email)) = ?', [$email]); }
        })->exists();
        if ($outsiders) { $this->conflict(); }
        DB::table('credential_groups')->where('id', $group->id)->update($changes);
        $ids = DB::table('users')->where('credential_group_id', $group->id)->pluck('id');
        DB::table('users')->whereIn('id', $ids)->where('id', '<>', $user->id)->update($changes);
        if (array_key_exists('password', $changes)) {
            DB::table('users')->whereIn('id', $ids)->update(['password_otp' => null, 'expire_at' => null]);
        }
        DB::table('admins')->whereIn('user_id', $ids)->update($changes);
        // Staged credentials must never resurrect an old password or old identifiers.
        DB::table('user_otps')->where('credential_group_id', $group->id)
            ->where('type', 'register')->where('is_verified', false)->update(['expire_at' => now()]);
        $user->forceFill($changes);
    }
}
