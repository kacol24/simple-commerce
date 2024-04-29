<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAmount extends Model
{
    protected $fillable = [
        'order_id',
        'amountable_type',
        'name',
        'amount',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];
}
