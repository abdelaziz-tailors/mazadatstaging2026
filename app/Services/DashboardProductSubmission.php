<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Short-lived form replay protection, isolated from mobile requests. */
class DashboardProductSubmission
{
    public static function token(string $scope): string
    {
        return Crypt::encryptString(json_encode([
            'scope' => $scope, 'admin' => auth('admin')->id(), 'nonce' => (string) Str::uuid(),
            'issued_at' => now()->timestamp,
        ]));
    }

    public static function run(Request $request, string $scope, Closure $save)
    {
        // Preserve compatibility with older Dashboard integrations.
        if (! $request->filled('dashboard_submission')) {
            return $save();
        }
        try {
            $payload = json_decode(Crypt::decryptString($request->input('dashboard_submission')), true);
            if (($payload['scope'] ?? null) !== $scope
                || ($payload['admin'] ?? null) !== auth('admin')->id()
                || ($payload['issued_at'] ?? 0) <= now()->subDay()->timestamp) {
                throw new \UnexpectedValueException();
            }
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['dashboard_submission' => trans('app.dashboard_submission_invalid')]);
        }

        $key = 'dashboard-product:'.hash('sha256', $request->input('dashboard_submission'));
        return Cache::lock($key.':lock', 120)->block(5, function () use ($key, $save) {
            if ($url = Cache::get($key)) {
                return redirect($url);
            }
            $response = $save();
            Cache::put($key, $response->getTargetUrl(), now()->addDay());
            return $response;
        });
    }
}
