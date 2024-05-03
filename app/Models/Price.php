<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use LogsActivity;

    protected $fillable = [
        'customer_group_id',
        'priceable_type',
        'priceable_id',
        'cost_price',
        'price',
        'tier',
    ];
}
