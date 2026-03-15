<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     echo "Home Page";
// });

Route::get('/', [HomeController::class, 'index']);
Route::get('/product-detail/', [HomeController::class, 'productDetails']);

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        echo "Admin Dashboard";
    });
    Route::resource('admin/products', ProductController::class);
});


Route::get('/cart', function () {
    echo "Cart Page";
});

Route::get('/checkout', function () {
    echo "Checkout Page";
});
