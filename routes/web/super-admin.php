<?php

use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\PlatformSettingsController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SuperAdminDashboardController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\SystemMaintenanceController;
use App\Http\Controllers\Admin\DeliveryEngineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/admins', [AdminManagementController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminManagementController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminManagementController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [AdminManagementController::class, 'edit'])->name('admins.edit');
    Route::put('/admins/{admin}', [AdminManagementController::class, 'update'])->name('admins.update');
    Route::patch('/admins/{admin}/suspend', [AdminManagementController::class, 'suspend'])->name('admins.suspend');
    Route::delete('/admins/{admin}', [AdminManagementController::class, 'destroy'])->name('admins.destroy');

    Route::get('/settings', [PlatformSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [PlatformSettingsController::class, 'update'])->name('settings.update');

    Route::get('/delivery-management/zones', [DeliveryEngineController::class, 'zones'])->name('delivery.zones.index');
    Route::post('/delivery-management/zones', [DeliveryEngineController::class, 'storeZone'])->name('delivery.zones.store');
    Route::put('/delivery-management/zones/{zone}', [DeliveryEngineController::class, 'updateZone'])->name('delivery.zones.update');
    Route::get('/delivery-management/rules', [DeliveryEngineController::class, 'rules'])->name('delivery.rules.index');
    Route::post('/delivery-management/rules', [DeliveryEngineController::class, 'storeRule'])->name('delivery.rules.store');
    Route::put('/delivery-management/rules/{rule}', [DeliveryEngineController::class, 'updateRule'])->name('delivery.rules.update');
    Route::get('/delivery-management/simulator', [DeliveryEngineController::class, 'simulator'])->name('delivery.simulator');
    Route::get('/delivery-management/analytics', [DeliveryEngineController::class, 'analytics'])->name('delivery.analytics');
    Route::get('/delivery-management/rule-history', [DeliveryEngineController::class, 'history'])->name('delivery.history');

    Route::get('/logs/activity', [SystemLogController::class, 'activityLogs'])->name('logs.activity');
    Route::get('/logs/api', [SystemLogController::class, 'apiLogs'])->name('logs.api');
    Route::get('/logs/security', [SystemLogController::class, 'securityLogs'])->name('logs.security');

    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::get('/maintenance', [SystemMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance/backup', [SystemMaintenanceController::class, 'backup'])->name('maintenance.backup');
    Route::get('/maintenance/backups/{file}', [SystemMaintenanceController::class, 'download'])->name('maintenance.download');
    Route::post('/maintenance/clear-compiled', [SystemMaintenanceController::class, 'clearCompiled'])->name('maintenance.clear-compiled');
});
