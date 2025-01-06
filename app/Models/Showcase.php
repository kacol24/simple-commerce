<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Showcase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'is_active',
        'title',
        'description',
        'start_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_at'  => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function getStatusAttribute()
    {
        return $this->is_active
            && (is_null($this->start_at) || $this->start_at->lte(now()))
            && (is_null($this->ends_at) || $this->ends_at->gt(now()));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active')
                     ->where(function ($q) {
                         $q->whereNull('start_at')
                           ->orWhere('start_at', '<=', now());
                     })
                     ->where(function ($q) {
                         $q->whereNull('ends_at')
                           ->orWhere('ends_at', '>', now());
                     });
    }
}
