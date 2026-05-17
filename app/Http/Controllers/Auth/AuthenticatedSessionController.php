<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\RoleRedirector;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, RoleRedirector $redirector, OtpService $otps): RedirectResponse
    {
        $request->authenticate();

        if (config('services.otp.login_enabled')) {
            $user = $request->user();
            $remember = $request->boolean('remember');

            Auth::guard('web')->logout();
            $request->session()->put('otp_login_user_id', $user->id);
            $request->session()->put('otp_login_remember', $remember);
            $otps->send($user, 'login');

            return redirect()->route('login.otp')->with('status', 'A login OTP has been sent to your email.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($redirector->dashboardRouteName($request->user()), absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
