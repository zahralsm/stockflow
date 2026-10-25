<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancellationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerCatalogController; // <-- TAMBAHAN: Import Controller Katalog Customer
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // ==========================================
    // TAMBAHAN RUTE KHUSUS CUSTOMER
    // ==========================================
    Route::get('/katalog', [CustomerCatalogController::class, 'index'])
        ->name('customer.catalog');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::resource('purchases', PurchaseController::class)
        ->only(['index', 'store']);

    Route::resource('sales', SaleController::class)
        ->only(['index', 'store']);

    Route::middleware(AdminMiddleware::class)->group(function () {
        Route::resource('categories', CategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('products', ProductController::class)
            ->only(['store', 'update', 'destroy']);

        Route::resource('suppliers', SupplierController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/cancellations', [CancellationController::class, 'index'])
            ->name('cancellations.index');

        Route::post(
            '/sales/{sale}/cancel',
            [CancellationController::class, 'cancelSale']
        )->name('sales.cancel');

        Route::post(
            '/purchases/{purchase}/cancel',
            [CancellationController::class, 'cancelPurchase']
        )->name('purchases.cancel');
    });
});