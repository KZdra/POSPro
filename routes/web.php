<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Authenticated Routes (Kasir & Admin)
Route::middleware(['auth'])->group(function () {
    
    // POS Cashier Routes
    Route::get('/', [POSController::class, 'index'])->name('pos.index');
    Route::post('/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::get('/status/{orderId}', [POSController::class, 'checkStatus'])->name('pos.status');
    Route::get('/print-receipt/{orderId}', [POSController::class, 'printReceipt'])->name('pos.receipt');

    // Sales History & PDF Export (Accessible by Kasir & Admin)
    Route::get('/admin/history', [AdminController::class, 'history'])->name('admin.history');
    Route::get('/admin/history/export-pdf', [AdminController::class, 'exportPdf'])->name('admin.history.pdf');

    // Admin-Only Protected Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Master Data
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('users', UserController::class);

        // Store Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings');
        Route::post('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Webhook for payment (open route, no auth, no CSRF)
Route::post('/callbacks/payment', [POSController::class, 'webhookCallback']);

require __DIR__.'/auth.php';
