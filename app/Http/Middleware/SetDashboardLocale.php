<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SetDashboardLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.dashboard_locale', 'ar');

        if (LaravelLocalization::getCurrentLocale() !== $locale) {
            return redirect(LaravelLocalization::getLocalizedURL($locale, null, [], true));
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
