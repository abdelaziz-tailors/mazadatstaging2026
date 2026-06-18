<?php

namespace App\Http\Controllers\Dashboard\AdminAuth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Brian2694\Toastr\Facades\Toastr;

use App\Models\Admin;
use App\Http\Requests\Dashboard\Auth\LoginRequest;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    // use AuthenticatesUsers, LogsoutGuard {
    //     LogsoutGuard::logout insteadof AuthenticatesUsers;
    // }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/admin/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest', ['except' => 'logout']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLoginForm()
    {
        return view('dashboard.auth.login');
    }

    protected function login(LoginRequest $request)
    {
        $admin = Admin::where('email', $request->email)->first();
    
        if($admin === NULL)
        {
            Toastr::error('Wrong Email Address Or Password');
            return redirect()->back();
        }
        else if(!Hash::check($request->password, $admin->password))
        {
            Toastr::error('Wrong Email Address Or Password');
            return redirect()->back();
        }
        Auth::guard('admin')->login($admin, true);

        return redirect(LaravelLocalization::getLocalizedURL(config('app.dashboard_locale', 'ar'), '/admin/'));
    }

    protected function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(LaravelLocalization::getLocalizedURL(config('app.dashboard_locale', 'ar'), '/admin/login'));
    }
}
