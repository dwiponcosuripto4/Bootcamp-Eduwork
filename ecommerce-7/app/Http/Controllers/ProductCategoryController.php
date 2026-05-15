<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productCategories = ProductCategory::withCount('products')
            ->withSum('products as total_stock' , 'stock')
            ->get();
            
        return view('admin.product-category.index', compact('productCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     return view('admin.product-category.create');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
            // 'slug' => 'required|string|max:255|unique:product_categories,slug',
        ]);

        $slug = strtolower(str_replace(' ', '-', $request->name));

        ProductCategory::create([
            'name' => $request->name,
            'slug' => $slug,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        // return view('admin.product-category.edit', compact('productCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,' . $productCategory->id,
            'slug' => 'required|string|max:100|unique:product_categories,slug,' . $productCategory->id,
        ]);

        $productCategory->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $product_count = $productCategory->products()->count();
        if ($product_count > 0) {
            return redirect()->back()->withErrors(['error' => 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.']);
        }
        $productCategory->delete();
        return redirect()->back()->with('success', 'Product Category with ID ' . $productCategory->id . ' deleted successfully.');
    }
}
