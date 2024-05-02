<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }
}
