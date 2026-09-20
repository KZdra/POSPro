<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

// Authenticated Routes (Kasir & Admin)
Route::middleware(['auth'])->group(function () {
    
    // POS Cashier Routes
    Route::get('/', [POSController::class, 'index'])->name('pos.index');
    Route::post('/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/validate-coupon', [POSController::class, 'validateCoupon'])->name('pos.validate-coupon');
    Route::get('/status/{orderId}', [POSController::class, 'checkStatus'])->name('pos.status');
    Route::post('/pos/manual-settle/{orderId}', [POSController::class, 'manualSettle'])->name('pos.manual-settle');
    Route::post('/pos/orders/{orderId}/cancel', [POSController::class, 'cancelPendingOrder'])->name('pos.orders.cancel');
    Route::get('/print-receipt/{orderId}', [POSController::class, 'printReceipt'])->name('pos.receipt');
    Route::get('/kitchen-receipt/{orderId}', [POSController::class, 'kitchenReceipt'])->name('pos.kitchen-receipt');

    // Cashier Shift Management & Petty Cash
    Route::get('/pos/shift/current', [ShiftController::class, 'current'])->name('pos.shift.current');
    Route::post('/pos/shift/open', [ShiftController::class, 'open'])->name('pos.shift.open');
    Route::post('/pos/shift/close/{id?}', [ShiftController::class, 'close'])->name('pos.shift.close');
    Route::post('/pos/shift/movement', [ShiftController::class, 'addCashMovement'])->name('pos.shift.movement');

    // Customer / Member Quick Lookup
    Route::get('/pos/customers/search', [CustomerController::class, 'search'])->name('pos.customers.search');
    Route::post('/pos/customers/quick-create', [CustomerController::class, 'store'])->name('pos.customers.quick-create');

    // Sales History, Void & Export (Accessible by Kasir & Admin)
    Route::get('/admin/history', [AdminController::class, 'history'])->name('admin.history');
    Route::get('/admin/history/export-pdf', [AdminController::class, 'exportPdf'])->name('admin.history.pdf');
    Route::get('/admin/history/export-csv', [AdminController::class, 'exportCsv'])->name('admin.history.csv');
    Route::post('/admin/orders/{orderId}/void', [AdminController::class, 'voidOrder'])->name('admin.orders.void');

    // Admin-Only Protected Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // Master Data
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('users', UserController::class);
        Route::resource('coupons', CouponController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('/shifts', [ShiftController::class, 'index'])->name('admin.shifts.index');

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
