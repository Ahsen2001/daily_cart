<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketReplyController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])->name('notifications.unread');

    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support.tickets.index');
    Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support.tickets.create');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support.tickets.store');
    Route::get('/support-tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support.tickets.show');
    Route::post('/support-tickets/{ticket}/replies', [SupportTicketReplyController::class, 'store'])->name('support.tickets.replies.store');
});
