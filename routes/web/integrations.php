<?php

use App\Http\Controllers\Integrations\GoogleMapsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('integrations/maps')->name('maps.')->group(function () {
    Route::post('/geocode', [GoogleMapsController::class, 'geocode'])->name('geocode');
    Route::post('/reverse-geocode', [GoogleMapsController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::post('/distance', [GoogleMapsController::class, 'distance'])->name('distance');
});
