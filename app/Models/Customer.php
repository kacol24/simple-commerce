<?php

namespace App\Models;

use App\Models\Concerns\HasPhone;
use App\Models\Concerns\HasWhatsapp;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasWhatsapp;
    use LogsActivity;
    use SoftDeletes;
    use HasPhone;

    protected $fillable = [
        'customer_group_id',
        'is_active',
        'name',
        'phone',
    ];

    protected $appends = [
        'name_with_phone',
    ];

    public function getNameWithPhoneAttribute()
    {
        $parts = [];
        if ($this->phone) {
            $parts[] = '['.$this->phone.']';
        }
        $parts[] = $this->name;

        return implode(' ', $parts);
    }

    public function getFriendlyPhoneAttribute()
    {
        return $this->friendlyPhone($this->phone);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class)->oldest();
    }
}
