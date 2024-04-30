<?php

namespace App\Models;

use App\Models\Concerns\HasWhatsapp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public function getFriendlyPhoneAttribute()
    {
        $chars = collect(str_split($this->phone));
        $split = $chars->reverse()->split(3)->reverse();

        $phone = collect();
        foreach ($split as $section) {
            $phone->push($section->reverse()->implode(''));
        }

        return $phone->implode('-');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
