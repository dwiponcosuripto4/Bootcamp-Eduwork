<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $cartItems = Cart::with('product')
                ->where('user_id', Auth::id())
                ->get();

            $cartTotal = $cartItems->sum(function ($cartItem) {
                return (float) $cartItem->product->price * (int) $cartItem->quantity;
            });

            return view('cart', compact('cartItems', 'cartTotal'));
        } else {
            return redirect()->route('login')->with('error', 'Please login to view your cart');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if(Auth::check()) {
            $user = Auth::user();
            $product = Product::find($request->product_id);
            if(!$product) {
                return redirect()->back()->with('error', 'Product not found');
            }
            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $request->product_id)
                ->first();
            if($cartItem) {
                $cartItem->quantity += 1;
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $request->product_id,
                    'quantity' => 1,
                ]);
            }
            return redirect()->back()->with('success', 'Product added to cart successfully');
        } else {
            return redirect()->route('login')->with('error', 'Please login to add products to cart');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        // update quantity cart item
        $cartItem = Cart::find($id);
        if(!$cartItem) {
            return redirect()->back()->with('error', 'Cart item not found');
        } else {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            return redirect()->back()->with('success', 'Cart item updated successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cartItem = Cart::find($id);
        if(!$cartItem) {
            return redirect()->back()->with('error', 'Cart item not found');
        } else {
            $cartItem->delete();
            return redirect()->back()->with('success', 'Cart item removed successfully');
        }
    }
}
