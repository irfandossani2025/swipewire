<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductFileChunkController as AdminProductFileChunkController;
use App\Http\Controllers\Admin\ProductFileController as AdminProductFileController;
use App\Http\Controllers\Admin\SeoSettingsController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ProductAssistantController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupportRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/products/{product:slug}', [StorefrontController::class, 'show'])->name('storefront.show');
Route::get('/downloads/{productFile}', DownloadController::class)->middleware(['auth', 'active'])->name('downloads.show');
Route::get('/account', CustomerDashboardController::class)->middleware(['auth', 'active'])->name('customer.dashboard');
Route::view('/register', 'auth.register')->middleware('guest')->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('guest')->name('register.store');
Route::view('/login', 'auth.login')->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login.store');
Route::post('/products/{product:slug}/checkout', [CheckoutController::class, 'store'])->middleware(['auth', 'active'])->name('checkout.store');
Route::get('/checkout/paypal/return', [CheckoutController::class, 'paypalReturn'])->middleware(['auth', 'active'])->name('checkout.paypal.return');
Route::get('/checkout/paypal/cancel', [CheckoutController::class, 'paypalCancel'])->middleware(['auth', 'active'])->name('checkout.paypal.cancel');
Route::view('/support', 'support')->name('support');
Route::post('/support', [SupportRequestController::class, 'store'])->middleware('throttle:5,1')->name('support.store');
Route::post('/assistant', [ProductAssistantController::class, 'store'])->middleware('throttle:20,1')->name('assistant.store');

Route::middleware(['auth', 'admin'])->prefix('admin')->as('admin.')->group(function (): void {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('products', AdminProductController::class)->except(['show', 'destroy']);
    Route::post('/products/{product}/files', [AdminProductFileController::class, 'store'])->name('products.files.store');
    Route::post('/products/{product}/uploads', [AdminProductFileChunkController::class, 'start'])->name('products.uploads.start');
    Route::post('/products/{product}/uploads/{upload}/chunks', [AdminProductFileChunkController::class, 'storeChunk'])->whereUuid('upload')->name('products.uploads.chunks.store');
    Route::post('/products/{product}/uploads/{upload}/complete', [AdminProductFileChunkController::class, 'complete'])->whereUuid('upload')->name('products.uploads.complete');
    Route::delete('/products/{product}/files/{productFile}', [AdminProductFileController::class, 'destroy'])->name('products.files.destroy');
    Route::get('/seo', [SeoSettingsController::class, 'edit'])->name('seo.edit');
    Route::put('/seo', [SeoSettingsController::class, 'update'])->name('seo.update');
    Route::get('/subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
});
