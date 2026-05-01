<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'status',
        'shipping_address',
        'total_amount',
        'user_id',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
