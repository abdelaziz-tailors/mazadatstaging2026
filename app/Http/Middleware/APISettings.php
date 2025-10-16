<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TranslationHelper;

class APISettings
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
        $api_key = $request->header('x-api-key');
        if($api_key == 'SIv5q09xLI689LNoALEh2D4Af/TsFkoypEMd/2XdtvGPfKHmU6HENZuuBgaBQKXM')
        {
            $locale = $request->header('Accept-Language');
            if(!$locale)
            {
                $locale = config('app.locale');
            }

            $langs = ['en', 'ar'];

            if (!in_array($locale, $langs))
            {
                return response()->json(['success'=>false, 'code'=>403, 'message'=>TranslationHelper::translate('language_not_supported')]);
            }
            $this->app->setLocale($locale);

            return $next($request);
        }
        else
        {
            return response()->json(['success'=>false, 'code'=>403, 'message'=>TranslationHelper::translate('unauthorized_access')]);
        }
    }
}
