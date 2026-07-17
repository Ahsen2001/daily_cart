<?php

use App\Http\Controllers\Integrations\PayHereController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/refund-policy', [PageController::class, 'refundPolicy'])->name('pages.refund-policy');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('pages.privacy-policy');
Route::get('/terms-and-conditions', [PageController::class, 'termsAndConditions'])->name('pages.terms-and-conditions');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('pages.contact.store');
Route::get('/offers', [PageController::class, 'offers'])->name('pages.offers');
Route::get('/categories', [PageController::class, 'categories'])->name('categories.index');
Route::get('/products', [PageController::class, 'products'])->name('products.index');
Route::get('/products/{product}', [PageController::class, 'product'])->name('products.show');

Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');
Route::post('/payment/payhere/notify', [PayHereController::class, 'notify'])->name('payhere.notify');
