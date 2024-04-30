<?php

use App\Pipelines\Order\Calculate;
use App\Pipelines\Order\CalculateDiscounts;
use App\Pipelines\Order\CalculateFees;
use App\Pipelines\Order\CalculateSubtotal;

return [
    'order' => [
        'pipelines' => [
            CalculateSubtotal::class,
            CalculateDiscounts::class,
            CalculateFees::class,
            Calculate::class,
        ],
    ],
];
