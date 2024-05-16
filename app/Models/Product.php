<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    use LogsActivity;

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

    protected $with = [
        'variants',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

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
