<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'address',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
