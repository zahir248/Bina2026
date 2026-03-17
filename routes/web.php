<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\CheckoutPaymentController;
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
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\EventParticipantsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TicketScannerController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Models\Setting;
use App\Models\Event;

// Serve storage files via Laravel (works on cPanel where public/storage symlink may not work)
Route::get('/storage/serve/{path}', function (string $path) {
    $path = str_replace(['../', '..\\'], '', $path);
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.serve');

Route::get('/', function () {
    $countdownEnabled = Setting::get(SettingsController::KEY_COUNTDOWN_ENABLED, '1') === '1';
    $countdownTargetDatetime = Setting::get(SettingsController::KEY_COUNTDOWN_TARGET_DATETIME, '2026-06-15T00:00:00');
    $countdownEvents = [];

    // Countdown prioritises upcoming events; admin target is used only when no events exist
    if ($countdownEnabled) {
        $upcomingEvents = Event::where('status', 'active')
            ->where('end_datetime', '>', now())
            ->orderBy('start_datetime', 'asc')
            ->get();
        foreach ($upcomingEvents as $event) {
            if ($event->start_datetime && $event->end_datetime) {
                $countdownEvents[] = [
                    'datetime' => $event->start_datetime->format('Y-m-d\TH:i:s'),
                    'end' => $event->end_datetime->format('Y-m-d\TH:i:s'),
                    'name' => $event->name,
                ];
            }
        }
    }

    return view('client.home', compact('countdownEnabled', 'countdownTargetDatetime', 'countdownEvents'));
})->name('home');

Route::get('/gallery', function () {
    return view('client.gallery');
})->name('gallery');

Route::get('/about-bina', function () {
    return view('client.about');
})->name('about-bina');

Route::get('/career-spotlight', function () {
    return view('client.career-spotlight');
})->name('career-spotlight');

Route::get('/ibs-home', function () {
    return view('client.ibs-home');
})->name('ibs-home');

Route::get('/nextgen-bina', function () {
    return view('client.nextgen-bina');
})->name('nextgen-bina');

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
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/create-payment-intent', [CheckoutPaymentController::class, 'createPaymentIntent'])->name('checkout.createPaymentIntent');
    Route::post('/checkout/update-intent-amount', [CheckoutPaymentController::class, 'updateIntentAmount'])->name('checkout.updateIntentAmount');
    Route::get('/checkout/payment-success', [CheckoutPaymentController::class, 'success'])->name('checkout.paymentSuccess');
    Route::post('/checkout/apply-affiliate', [CartController::class, 'applyCheckoutAffiliate'])->name('checkout.applyAffiliate');
    Route::post('/checkout/remove-affiliate', [CartController::class, 'removeCheckoutAffiliate'])->name('checkout.removeAffiliate');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/purchase-history', [ProfileController::class, 'purchaseHistory'])->name('profile.purchaseHistory');
    Route::get('/profile/orders/{order}/modal', [ProfileController::class, 'orderModal'])->name('profile.orderModal');
    Route::post('/profile/orders/{order}/create-repay-intent', [CheckoutPaymentController::class, 'repayCreateIntent'])->name('profile.order.createRepayIntent');
    Route::post('/profile/orders/{order}/cancel', [ProfileController::class, 'cancelOrder'])->name('profile.order.cancel');
    Route::post('/profile/orders/{order}/refund', [ProfileController::class, 'refundOrder'])->name('profile.order.refund');
    Route::get('/profile/orders/{order}/receipt', [ProfileController::class, 'downloadReceipt'])->name('profile.order.receipt');
    Route::get('/profile/orders/{order}/qr-code/{index}', [ProfileController::class, 'downloadQrCode'])->name('profile.order.qrCode');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
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

// Ticket Scanner (public – no login required)
Route::get('/scanner', [TicketScannerController::class, 'index'])->name('admin.scanner');
Route::post('/scanner/validate', [TicketScannerController::class, 'validateTicket'])->name('admin.scanner.validate');

// Admin Routes (login required)
Route::middleware(['prevent_guest_admin_when_not_maintenance', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/event-participants', [EventParticipantsController::class, 'index'])->name('event-participants');
    Route::get('/event-participants/export', [EventParticipantsController::class, 'export'])->name('event-participants.export');
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
        Route::post('/upload-content-image', [EventController::class, 'uploadContentImage'])->name('upload-content-image');
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

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/{order}/modal', [OrderController::class, 'modal'])->name('orders.modal');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'downloadReceipt'])->name('orders.receipt');
    Route::get('/orders/{order}/qr-code/{index}', [OrderController::class, 'downloadQrCode'])->name('orders.qrCode');
    Route::post('/orders/{order}/refund-approve', [OrderController::class, 'approveRefund'])->name('orders.refund.approve');
    Route::post('/orders/{order}/refund-reject', [OrderController::class, 'rejectRefund'])->name('orders.refund.reject');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');

    // Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/email', [LogController::class, 'emailLog'])->name('email');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Profile (logged-in admin)
    Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
});