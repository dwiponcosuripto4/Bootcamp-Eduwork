<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartTotal = 0;
        if(Auth::check()) {
            $user = Auth::user();
            $cartItems = Cart::where('user_id', $user->id)
            ->with('product')
            ->get();
            foreach($cartItems as $item) {
                $cartTotal += $item->product->price * $item->quantity;
            }
            return view('checkout', compact('cartItems', 'cartTotal'));
        } else {
            return redirect()->route('login')->with('error', 'Please login to proceed to checkout');
        }
        return view('checkout');
    }
}
