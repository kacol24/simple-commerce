<?php

namespace App\Models;

use App\Models\Concerns\HasPrices;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasPrices;

    protected $fillable = [
        'product_id',
        'sku',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}
