<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RiderRegistrationController;
use App\Http\Controllers\Auth\VendorRegistrationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('vendor/register', [VendorRegistrationController::class, 'create'])
        ->name('vendor.register');

    Route::post('vendor/register', [VendorRegistrationController::class, 'store'])
        ->name('vendor.register.store');

    Route::get('rider/register', [RiderRegistrationController::class, 'create'])
        ->name('rider.register');

    Route::post('rider/register', [RiderRegistrationController::class, 'store'])
        ->name('rider.register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('login/otp', [OtpController::class, 'loginChallenge'])->name('login.otp');
    Route::post('login/otp', [OtpController::class, 'verifyLogin'])->name('login.otp.verify');
    Route::post('login/otp/resend', [OtpController::class, 'resendLogin'])->name('login.otp.resend');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('email/verification-otp', [OtpController::class, 'sendEmailVerification'])
        ->middleware('throttle:3,1')
        ->name('verification.otp.send');

    Route::post('email/verification-otp/verify', [OtpController::class, 'verifyEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.otp.verify');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
