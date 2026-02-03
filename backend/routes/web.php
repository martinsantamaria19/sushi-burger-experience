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

// Case-insensitive public menu route
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
