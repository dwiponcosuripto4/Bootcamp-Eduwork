<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'product_category_id',
    ];
    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
}
