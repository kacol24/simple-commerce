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
        'is_active',
        'name',
        'phone',
    ];

    protected $appends = [
        'name_with_phone',
    ];

    public function getNameWithPhoneAttribute()
    {
        return '['.$this->phone.'] '.$this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
