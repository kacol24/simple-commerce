<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_default',
        'is_reseller',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_reseller' => 'boolean',
    ];
}
