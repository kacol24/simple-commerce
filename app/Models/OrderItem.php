<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'purchasable_type',
        'purchasable_id',
        'title',
        'short_description',
        'option',
        'sku',
        'price',
        'quantity',
        'sub_total',
        'discount_total',
        'total',
        'notes',
        'sort',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function purchasable()
    {
        return $this->morphTo();
    }

    public function getSellPriceAttribute()
    {
        return $this->price - $this->discount_total;
    }
}
