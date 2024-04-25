<?php

namespace App\Models\Concerns;

trait HasWhatsapp
{
    public function getWhatsappUrlAttribute()
    {
        return 'https://wa.me/62'.$this->phone;
    }
}
