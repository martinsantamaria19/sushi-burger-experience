<?php

use App\Http\Controllers\Admin\ContactSubmissionController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuoteSubmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Público
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/soluciones', [HomeController::class, 'soluciones'])->name('soluciones');
Route::get('/instalaciones', [HomeController::class, 'instalaciones'])->name('instalaciones');
Route::get('/contacto', [HomeController::class, 'contacto'])->name('contacto');
Route::post('/contacto', [ContactController::class, 'enviarContacto'])->name('contacto.enviar');
Route::get('/cotizar', [HomeController::class, 'cotizar'])->name('cotizar');
Route::post('/cotizar', [ContactController::class, 'enviarCotizacion'])->name('cotizar.enviar');

// Admin: login (sin auth)
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Admin: panel (con auth)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
    Route::get('/contents/{group}/edit', [ContentController::class, 'edit'])->name('contents.edit');
    Route::put('/contents/{group}', [ContentController::class, 'update'])->name('contents.update');
    Route::get('/contacts', [ContactSubmissionController::class, 'index'])->name('contacts.index');
    Route::get('/quotes', [QuoteSubmissionController::class, 'index'])->name('quotes.index');
});
