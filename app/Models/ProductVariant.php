<?php

namespace App\Models;

use App\Models\Concerns\HasPrices;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasPrices;
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'sku',
    ];

    protected $with = [
        'prices',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}
