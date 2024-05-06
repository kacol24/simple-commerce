<?php

namespace App\Models\Concerns;

trait FormatsMoney
{
    private function formatMoney($value)
    {
        $format = [];
        if (config('commerce.show_currency')) {
            $format[] = 'Rp';
        }
        $format[] = $this->numberFormat($value);

        return implode('', $format);
    }

    private function numberFormat($value)
    {
        return number_format($value, 0, ',', '.');
    }
}
