<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $products = Product::with('product_category')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            // ->where('stock', '>', 10000)
            ->orderBy('price', 'desc')
            ->paginate(8);

        return view('home', compact('products'));
    }
    public function productDetails($slug)
    {
        $product = Product::with('product_category')
            ->where('slug', $slug)
            ->firstOrFail();
        $prouct_recommendation = Product::with('product_category')
            ->where('product_category_id', $product->product_category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('product_detail', compact('product', 'prouct_recommendation'));
    }
}
