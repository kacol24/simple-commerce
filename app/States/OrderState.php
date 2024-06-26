<?php

namespace App\States;

use App\States\Order\Cancelled;
use App\States\Order\Completed;
use App\States\Order\Draft;
use App\States\Order\Paid;
use App\States\Order\PartialPayment;
use App\States\Order\PendingPayment;
use App\States\Order\Processing;
use App\States\Order\Refunded;
use App\States\Order\Shipped;
use App\States\Order\UnderShipment;
use Illuminate\Support\Str;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class OrderState extends State
{
    public function friendlyName()
    {
        return Str::headline(class_basename($this));
    }

    abstract public function color();

    abstract public function canEditAddress(): Bool;

    abstract public function canEditOrder(): Bool;

    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    public static function config(): StateConfig
    {
        return parent::config()
                     ->default(Draft::class)
                     ->allowTransition([Draft::class, Processing::class],
                         PendingPayment::class)
                     ->allowTransition([Draft::class, PendingPayment::class, Processing::class],
                         PartialPayment::class)
                     ->allowTransition([Draft::class, PendingPayment::class, PartialPayment::class, Processing::class],
                         Paid::class)
                     ->allowTransition([PendingPayment::class, PartialPayment::class, Paid::class],
                         Processing::class)
                     ->allowTransition(Processing::class,
                         UnderShipment::class)
                     ->allowTransition(UnderShipment::class,
                         Shipped::class)
                     ->allowTransition(Shipped::class,
                         Completed::class)
            // COD
                     ->allowTransition(Draft::class,
                Processing::class)
                     ->allowTransition(Shipped::class,
                         Paid::class)
                     ->allowTransition(Paid::class,
                         Completed::class)
            // Failure
                     ->allowTransition([Draft::class, PendingPayment::class, Processing::class],
                Cancelled::class)
                     ->allowTransition([PartialPayment::class, Paid::class],
                         Refunded::class)
                     ->registerState([
                         Draft::class,
                         PendingPayment::class,
                         PartialPayment::class,
                         Paid::class,
                         Processing::class,
                         UnderShipment::class,
                         Shipped::class,
                         Completed::class,
                         Cancelled::class,
                         Refunded::class,
                     ]);
    }
}
