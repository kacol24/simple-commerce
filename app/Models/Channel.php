<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'is_default',
        'name',
        'url',
        'receipt_footer',
        'socials',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'socials'    => 'array',
    ];
}
