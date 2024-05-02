<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_active',
        'is_featured',
        'title',
        'short_description',
        'long_description',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'default_sku',
        'default_price',
        'default_cost_price',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function defaultVariant()
    {
        return $this->variants()->first();
    }

    public function getDefaultSkuAttribute()
    {
        return $this->defaultVariant()->sku;
    }

    public function getDefaultPriceAttribute()
    {
        return $this->defaultVariant()->basePrices()->first()->price;
    }

    public function getDefaultCostPriceAttribute()
    {
        return $this->defaultVariant()->basePrices()->first()->cost_price;
    }
}
