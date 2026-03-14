<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo "Home Page";
});

Route::get('/products', function () {
    echo "Products Page";
});

Route::get('/cart', function () {
    echo "Cart Page";
});

Route::get('/checkout', function () {
    echo "Checkout Page";
});
