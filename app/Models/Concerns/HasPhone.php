<?php

namespace App\Models\Concerns;

trait HasPhone
{
    public function friendlyPhone($phone)
    {
        $chars = collect(str_split($phone));
        $split = $chars->reverse()->split(3)->reverse();

        $formattedPhone = collect();
        foreach ($split as $section) {
            $formattedPhone->push($section->reverse()->implode(''));
        }

        return $formattedPhone->implode('-');
    }
}
