<?php

namespace App\Http\Controllers\Dashboard\AdminAuth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Helpers\MobileNumber;
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
        $identifier = $request->input('email') ?: $request->input('phone');
        $key = 'dashboard-login:'.hash('sha256', strtolower((string) MobileNumber::normalize($identifier)).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Toastr::error('Wrong Email Address Or Password');
            return redirect()->back();
        }
        $admin = $request->filled('email')
            ? Admin::where('email', $request->email)->first()
            : MobileNumber::find(Admin::class, $request->phone);
        $passwordMatches = Hash::check($request->password, $admin ? $admin->password
            : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (! $admin || ! $passwordMatches) {
            RateLimiter::hit($key, 60);
            Toastr::error('Wrong Email Address Or Password');
            return redirect()->back();
        }
        RateLimiter::clear($key);
        Auth::guard('admin')->login($admin, true);
        $request->session()->regenerate();

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
