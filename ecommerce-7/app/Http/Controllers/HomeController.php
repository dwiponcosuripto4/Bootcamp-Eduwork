<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedCategory = $request->input('product_category');

        $productCategories = ProductCategory::orderBy('name')->get();

        $products = Product::with('product_category')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->when($selectedCategory, function ($query, $selectedCategory) {
                return $query->whereHas('product_category', function ($categoryQuery) use ($selectedCategory) {
                    $categoryQuery->where('slug', $selectedCategory);
                });
            })
            // ->where('stock', '>', 10000)
            ->orderBy('price', 'desc')
            ->paginate(8);

        $products->appends($request->query());

        return view('home', compact('products', 'productCategories', 'selectedCategory'));
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
