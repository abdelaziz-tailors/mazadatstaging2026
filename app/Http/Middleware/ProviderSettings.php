<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TranslationHelper;

class ProviderSettings
{
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->type != 'provider')
        {
            return response()->json(['success'=>false, 'code'=>403, 'message'=>TranslationHelper::translate('unauthorized_access')]);
        }
        else {
            return $next($request);
        }
    }
}
