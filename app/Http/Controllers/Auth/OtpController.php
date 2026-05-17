<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function loginChallenge(Request $request): View
    {
        abort_unless($request->session()->has('otp_login_user_id'), 403);

        return view('auth.otp-login');
    }

    public function verifyLogin(Request $request, OtpService $otps, RoleRedirector $redirector): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $userId = $request->session()->get('otp_login_user_id');
        $user = User::findOrFail($userId);

        $otps->verify($user->email, $request->code, 'login');
        Auth::login($user, (bool) $request->session()->pull('otp_login_remember', false));
        $request->session()->forget('otp_login_user_id');
        $request->session()->regenerate();

        return redirect()->intended(route($redirector->dashboardRouteName($user), absolute: false));
    }

    public function resendLogin(Request $request, OtpService $otps): RedirectResponse
    {
        $user = User::findOrFail($request->session()->get('otp_login_user_id'));
        $otps->send($user, 'login');

        return back()->with('status', 'A new login OTP has been sent.');
    }

    public function sendEmailVerification(Request $request, OtpService $otps): RedirectResponse
    {
        $otps->send($request->user(), 'email_verification');

        return back()->with('status', 'verification-otp-sent');
    }

    public function verifyEmail(Request $request, OtpService $otps): RedirectResponse
    {
        $request->validate(['code' => ['required', 'digits:6']]);
        $otps->verify($request->user()->email, $request->code, 'email_verification');

        return redirect()->intended(route('dashboard', absolute: false))->with('status', 'Email verified successfully.');
    }
}
