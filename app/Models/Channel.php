<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_default',
        'name',
        'url',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
