<?php

namespace App\Http\Middleware;

use App\Helpers\TranslationHelper;
use Closure;
use Illuminate\Http\Request;

class RestrictVendorAccess
{
    /**
     * Vendors (auction subscribers / sellers in the app) must not access admin role metadata via the public API.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();
        if (! $user || $user->user_type !== 'vendor') {
            return $next($request);
        }

        if ($request->is('api/roles') || $request->is('api/roles/*')) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => TranslationHelper::translate('Un-Authorized Access'),
            ], 403);
        }

        return $next($request);
    }
}
