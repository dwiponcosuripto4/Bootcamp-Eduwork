<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'address' => 'required|string|max:255',
        ]);
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'user_id' => Auth::id(),
            'status' => 'pending',
            'shipping_address' => $request->address,
            'total_amount' => Cart::where('user_id', Auth::id())->with('product')->get()->sum(function ($cartItem) {
                return $cartItem->quantity * $cartItem->product->price;
            }),
        ]);
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->product->price,
            ]);
        }
        Cart::where('user_id', Auth::id())->delete();
        return redirect()->route('order.show', $order->order_number)->with('success', 'Order placed successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $order_number)
    {
        $order = Order::where('order_number', $order_number)
        ->where('user_id', Auth::id())
        ->with('items.product')
        ->firstOrFail();
        return view('order.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
