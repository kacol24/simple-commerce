<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'order',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
