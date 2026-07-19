<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Rider\RiderDashboardController;
use App\Http\Controllers\Rider\RiderDeliveryController;
use App\Http\Controllers\Rider\RiderEarningController;
use App\Http\Controllers\Rider\RiderReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/pending', [DashboardController::class, 'riderPending'])->name('pending');

    Route::middleware('rider.approved')->group(function () {
        Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
        Route::get('/reports', [RiderReportController::class, 'index'])->name('reports.index');
        Route::get('/deliveries', [RiderDeliveryController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/earnings', [RiderDeliveryController::class, 'earnings'])->name('deliveries.earnings');
        Route::get('/deliveries/{delivery}', [RiderDeliveryController::class, 'show'])->name('deliveries.show');
        Route::patch('/deliveries/{delivery}/accept', [RiderDeliveryController::class, 'accept'])->name('deliveries.accept');
        Route::patch('/deliveries/{delivery}/picked-up', [RiderDeliveryController::class, 'pickedUp'])->name('deliveries.picked-up');
        Route::patch('/deliveries/{delivery}/on-the-way', [RiderDeliveryController::class, 'onTheWay'])->name('deliveries.on-the-way');
        Route::post('/deliveries/{delivery}/delivered', [RiderDeliveryController::class, 'delivered'])->name('deliveries.delivered');
        Route::post('/deliveries/{delivery}/proof', [RiderDeliveryController::class, 'replaceProof'])->name('deliveries.proof.replace');
        Route::patch('/deliveries/{delivery}/failed', [RiderDeliveryController::class, 'failed'])->name('deliveries.failed');
        Route::post('/location', [RiderDeliveryController::class, 'location'])->name('location.store');
        Route::get('/earnings', [RiderEarningController::class, 'index'])->name('earnings.index');
    });
});
