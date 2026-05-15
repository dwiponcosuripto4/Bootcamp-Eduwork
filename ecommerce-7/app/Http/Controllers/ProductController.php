<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('product_category')
            ->orderBy('id', 'desc');
            if(request('search')) {
                $products->where('name', 'like', '%' . request('search') . '%');
            }
            $products = $products->paginate(10);
        return view('admin.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productCategories = ProductCategory::all();
        return view('admin.product.create', compact('productCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'product_category_id' => 'required|exists:product_categories,id',
        ]);

        if ($request->hasFile('image')) {
            // Handle file upload
            $image_name = time() . '_' . $request->file('image')->getClientOriginalName();
            // $request->file('image')->storeAs('public/products', $image_name);
            // Upload with storage disk 'public' configuration in config/filesystems.php
            Storage::disk('images')->putFileAs('products', $request->file('image'), $image_name);
        } 

        $slug = strtolower(str_replace(' ', '-', $request->name)).'-'.uniqid();
        Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => 'products/' . $image_name,
            'product_category_id' => $request->product_category_id,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $productCategories = ProductCategory::all();
        return view('admin.product.edit', compact('product', 'productCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|string',
            'product_category_id' => 'required|exists:product_categories,id',
        ]);
        $imageName = $product->image;
        if ($request->filled('image') && str_contains((string) $request->image, ';base64,')) {
            $imageData = $request->image;
            $mime = str_replace('data:', '', explode(';', $imageData)[0]);
            $extensionMap = [
                'image/jpeg' => 'jpg',
                'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            $extension = $extensionMap[$mime] ?? 'webp';
            [, $imageData] = explode(';', $imageData);
            [, $imageData] = explode(',', $imageData);
            $imageData = base64_decode($imageData);
            $imageName = 'products/' . uniqid() . '.' . $extension;
            
            Storage::disk('images')->put($imageName, $imageData);

            if ($product->image && Storage::disk('images')->exists($product->image)) {
                Storage::disk('images')->delete($product->image);
            }
        }
        $product->update(
            [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'image' => $imageName,
                'product_category_id' => $request->product_category_id,
            ]
        );

        return redirect()->route('products.index')->with('success','Product with ID ' . $product->id . ' updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $id = $product->id;
        if ($product->order_items()->count() > 0) {
            return redirect()->route('products.index')->with('error', 'Product with ID ' . $id . ' cannot be deleted because it has associated orders.');
        }
        if ($product->image && Storage::disk('images')->exists($product->image)) {
            Storage::disk('images')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product with ID ' . $id . ' deleted successfully.');
    }
}
