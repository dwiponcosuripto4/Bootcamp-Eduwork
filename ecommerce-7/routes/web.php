<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::resource('cart', CartController::class);
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::post('make-order', [OrderController::class, 'store'])->name('make.order');
Route::get('/order/{order_number}', [OrderController::class, 'show'])->name('order.show');

Route::get('/product-detail/{slug}', [HomeController::class, 'productDetails'])->name('product.detail');

Route::middleware('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::post('/orders/{order}/update-status', [OrderController::class, 'update'])->name('orders.updateStatus');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
