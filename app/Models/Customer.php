<?php

namespace App\Models;

use App\Models\Concerns\HasWhatsapp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasWhatsapp;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
    ];
}
