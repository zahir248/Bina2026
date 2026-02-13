<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\EventCategoryController as ClientEventCategoryController;
use App\Http\Controllers\Client\EventController as ClientEventController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\EventPersonnelController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\AffiliateCodeController;

// Serve storage files via Laravel (works on cPanel where public/storage symlink may not work)
Route::get('/storage/serve/{path}', function (string $path) {
    $path = str_replace(['../', '..\\'], '', $path);
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.serve');

Route::get('/', function () {
    return view('client.home');
})->name('home');

Route::get('/gallery', function () {
    return view('client.gallery');
})->name('gallery');

Route::get('/about-bina', function () {
    return view('client.about');
})->name('about-bina');

Route::get('/event/{slug}', [ClientEventController::class, 'show'])->name('events.show');

// Cart Routes (specific paths before /cart/{id} to avoid 404)
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::get('/cart/update-quantities', function () {
        return redirect()->route('cart.index');
    });
    Route::post('/cart/update-quantities', [CartController::class, 'updateQuantities'])->name('cart.updateQuantities');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/apply-promo', [CartController::class, 'applyPromo'])->name('cart.applyPromo');
    Route::post('/cart/remove-promo', [CartController::class, 'removePromo'])->name('cart.removePromo');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/signup/google', [AuthController::class, 'redirectToGoogleSignup'])->name('signup.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/signup', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->name('signup.store');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Client API Routes
Route::get('/api/event-categories', [ClientEventCategoryController::class, 'index'])->name('api.event-categories');
Route::get('/api/events', [ClientEventController::class, 'index'])->name('api.events');
Route::get('/api/events/upcoming', [ClientEventController::class, 'upcoming'])->name('api.events.upcoming');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Events routes
    Route::prefix('events')->name('events.')->group(function () {
        // Categories
        Route::get('/categories', [EventCategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [EventCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{id}', [EventCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [EventCategoryController::class, 'destroy'])->name('categories.destroy');
        
        // Events
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::put('/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
        
        // Schedules
        Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules');
        Route::get('/schedules/event/{eventId}', [ScheduleController::class, 'getSchedules'])->name('schedules.get');
        Route::post('/schedules/event/{eventId}', [ScheduleController::class, 'saveSchedules'])->name('schedules.save');
        
        // Event Personnel
        Route::get('/personnel', [EventPersonnelController::class, 'index'])->name('personnel');
        Route::post('/personnel', [EventPersonnelController::class, 'store'])->name('personnel.store');
        Route::put('/personnel/{id}', [EventPersonnelController::class, 'update'])->name('personnel.update');
        Route::delete('/personnel/{id}', [EventPersonnelController::class, 'destroy'])->name('personnel.destroy');
        
        // Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    });
    
    // Promo Codes
    Route::get('/promo-codes', [PromoCodeController::class, 'index'])->name('promo-codes');
    Route::post('/promo-codes', [PromoCodeController::class, 'store'])->name('promo-codes.store');
    Route::put('/promo-codes/{id}', [PromoCodeController::class, 'update'])->name('promo-codes.update');
    Route::delete('/promo-codes/{id}', [PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');
    
    // Affiliate Codes
    Route::get('/affiliate-codes', [AffiliateCodeController::class, 'index'])->name('affiliate-codes');
    Route::post('/affiliate-codes', [AffiliateCodeController::class, 'store'])->name('affiliate-codes.store');
    Route::put('/affiliate-codes/{id}', [AffiliateCodeController::class, 'update'])->name('affiliate-codes.update');
    Route::delete('/affiliate-codes/{id}', [AffiliateCodeController::class, 'destroy'])->name('affiliate-codes.destroy');
});