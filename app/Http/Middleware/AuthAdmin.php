<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

use App\Helpers\TranslationHelper;
use App\Models\Department;

class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(!Auth::guard('admin')->check())
        {
            return redirect(app()->getLocale().'/admin/login');
        }
        else {
            if($request->is(app()->getLocale().'/admin/translations*')) {
                if (!Auth::guard('admin')->user()->can('manage translations')) {
                    abort('403', TranslationHelper::translate('THIS ACTION IS UNAUTHORIZED.'));
                }
            }
            else {
//                $departments = Department::select('id', 'name->'.app()->getLocale().' as name')->get();
                View::share(['departments' => '']);
            }
        }
        return $next($request);
    }
}
