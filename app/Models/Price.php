<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
        'customer_group_id',
        'priceable_type',
        'priceable_id',
        'cost_price',
        'price',
        'tier',
    ];
}
