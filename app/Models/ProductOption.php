<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOption extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'product_id',
        'name',
        'display_name',
    ];

    public function scopeShared($query)
    {
        return $query->whereNull('product_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->display_name ?? $this->name;
    }
}
