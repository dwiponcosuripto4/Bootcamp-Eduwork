<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     echo "Home Page";
// });

Route::get('/', function () {
    $products = [
        ['name' => 'Sepatu Lari', 'price' => 250000, 'image' => 'https://cdn.getswift.asia/unsafe/500x500/filters:format(webp):quality(80)/https://bo.asics.co.id/media/catalog/product/cache/4a5bef1eb0b3e9b20c2d6e32e87a7fc1/1/2/1203a763.100_3.jpg'],
        ['name' => 'Kaos Polos', 'price' => 85000, 'image' => 'https://berducdn.com/img/800/bsoai4w7bsoau5pwec_2/CduzDuG085WOQihsCdDCuZ2mm5nXzSS9OA8sgrd9xKjA.jpg'],
        ['name' => 'Tas Ransel', 'price' => 320000, 'image' => 'https://img.lazcdn.com/g/p/35002e4af2fe096ceb96e56c7ccf3e44.png_720x720q80.png'],
    ];
    return view('home', compact('products'));
});
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::get('/product-detail/{slug}', [HomeController::class, 'productDetails'])->name('product.detail');

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        echo "Admin Dashboard";
    });
    Route::resource('admin/products', ProductController::class);
});




Route::get('/checkout', function () {
    echo "Checkout Page";
});
