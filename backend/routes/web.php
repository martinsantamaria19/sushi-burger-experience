<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicMenuController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrRedirectController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\ResourceManagementController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MercadoPagoAccountController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Email Verification Routes
    Route::get('/verify-email/{token}', [\App\Http\Controllers\EmailVerificationController::class, 'verify'])->name('email.verify');
    Route::post('/resend-verification', [\App\Http\Controllers\EmailVerificationController::class, 'resend'])->name('email.resend');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::prefix('admin')->group(function() {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/restaurants', [DashboardController::class, 'restaurants'])->name('admin.restaurants');
        Route::post('/restaurants/switch', [DashboardController::class, 'switchRestaurant'])->name('admin.restaurants.switch');
        Route::get('/menu', [DashboardController::class, 'menu'])->name('admin.menu');
        Route::get('/qrs', [DashboardController::class, 'qrs'])->name('admin.qrs');
        Route::get('/users', [DashboardController::class, 'users'])->name('admin.users');
        Route::get('/analytics', [DashboardController::class, 'analytics'])->name('admin.analytics');
        Route::get('/settings', [DashboardController::class, 'settings'])->name('admin.settings');
        Route::get('/personalize', [DashboardController::class, 'personalize'])->name('admin.personalize');
        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('admin.subscription');
        Route::get('/resource-management', [ResourceManagementController::class, 'index'])->name('admin.resource-management');
        Route::post('/resource-management/update', [ResourceManagementController::class, 'updateResources'])->name('admin.resource-management.update');

        // Orders routes (admin)
        Route::prefix('orders')->name('admin.orders.')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('cancel');
        });

        // MercadoPago configuration routes (admin)
        Route::prefix('mercadopago')->name('admin.mercadopago.')->group(function () {
            Route::get('/', [MercadoPagoAccountController::class, 'index'])->name('index');
            Route::post('/', [MercadoPagoAccountController::class, 'store'])->name('store');
            Route::delete('/', [MercadoPagoAccountController::class, 'destroy'])->name('destroy');
            Route::post('/test', [MercadoPagoAccountController::class, 'test'])->name('test');
        });
    });

    // API-like routes for the dashboard
    Route::prefix('dashboard-api')->group(function () {
        Route::get('/analytics', [DashboardController::class, 'getAnalytics'])->name('api.analytics');
        Route::put('/company', [DashboardController::class, 'updateCompany'])->name('api.company.update');
        Route::apiResource('restaurants', RestaurantController::class);
        Route::apiResource('menus', MenuController::class);
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('qrcodes', QrCodeController::class);
        Route::apiResource('users', \App\Http\Controllers\UserController::class);
    });

    // Subscription routes
    Route::prefix('subscriptions')->group(function () {
        Route::post('/create-intent', [SubscriptionController::class, 'createIntent'])->name('subscription.create-intent');
        Route::post('/create-with-token', [SubscriptionController::class, 'createWithCardToken'])->name('subscription.create-with-token');
        Route::get('/current', [SubscriptionController::class, 'show'])->name('subscription.show');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        Route::post('/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
        Route::get('/payment-history', [SubscriptionController::class, 'paymentHistory'])->name('subscription.payment-history');
        Route::get('/success', [SubscriptionController::class, 'success'])->name('subscription.success');
        Route::get('/failure', [SubscriptionController::class, 'failure'])->name('subscription.failure');
        Route::get('/pending', [SubscriptionController::class, 'pending'])->name('subscription.pending');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Webhooks (sin autenticación, vienen de MercadoPago)
Route::post('/api/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/api/webhooks/mercadopago/orders', [MercadoPagoWebhookController::class, 'handleOrderPayment'])
    ->name('webhooks.mercadopago.orders')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Cart routes (públicas, sin autenticación requerida)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::get('/total', [CartController::class, 'getTotal'])->name('total');
});

// Order routes (públicas)
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::get('/{order}/payment/{token}', [OrderController::class, 'payment'])->name('payment');
    Route::get('/{order}/bank-transfer/{token}', [OrderController::class, 'bankTransfer'])->name('bank-transfer');
    Route::get('/success/{order}/{token}', [OrderController::class, 'success'])->name('success');
    Route::get('/failure', [OrderController::class, 'failure'])->name('failure');
    Route::get('/track', [OrderController::class, 'track'])->name('track');
    Route::get('/{order}/track/{token}', [OrderController::class, 'show'])->name('show');
});

// Payment routes (públicas)
Route::prefix('payments')->name('payments.')->group(function () {
    Route::post('/{order}/preference', [PaymentController::class, 'createPreference'])->name('create-preference');
    Route::post('/{order}/process-mercadopago', [PaymentController::class, 'processMercadoPagoPayment'])->name('process-mercadopago');
    Route::post('/{order}/bank-transfer', [PaymentController::class, 'processBankTransfer'])->name('bank-transfer');
    Route::post('/{payment}/proof', [PaymentController::class, 'uploadTransferProof'])->name('upload-proof');
    Route::get('/{payment}/verify', [PaymentController::class, 'verifyPayment'])->name('verify');
    Route::get('/bank-accounts', [PaymentController::class, 'getBankAccounts'])->name('bank-accounts');
});

// Case-insensitive public menu route (debe ir al final para no interferir con otras rutas)
Route::get('/{slug}', [PublicMenuController::class, 'show'])->name('public.menu');

// QR Scan Redirect & Tracking
Route::get('/scan/{slug}', [QrRedirectController::class, 'handle'])->name('qr.scan');

// Super Admin Routes
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\SuperAdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/assign-plan', [\App\Http\Controllers\SuperAdminController::class, 'assignPlan'])->name('users.assign_plan');
    Route::get('/plans', [\App\Http\Controllers\SuperAdminController::class, 'plans'])->name('plans');
    Route::put('/plans/{plan}', [\App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('plans.update');
    Route::get('/coupons', [\App\Http\Controllers\SuperAdminController::class, 'coupons'])->name('coupons');
    Route::post('/coupons', [\App\Http\Controllers\SuperAdminController::class, 'createCoupon'])->name('coupons.create');
    Route::patch('/coupons/{coupon}/toggle', [\App\Http\Controllers\SuperAdminController::class, 'toggleCoupon'])->name('coupons.toggle');
});
