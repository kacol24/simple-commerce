<?php

namespace App\Actions;

use App\DataObjects\AddOrUpdateOrderItemPayload;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;

class AddOrUpdateOrderItem extends AbstractAction
{
    public function execute(Model $order, AddOrUpdateOrderItemPayload $payload)
    {
        $subtotal = $payload->product->default_price * $payload->quantity;
        $discountTotal = $payload->discount;
        $total = $subtotal - $discountTotal;

        $orderItem = $order->items()->updateOrCreate([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => $payload->product->id,
        ], [
            'title'             => $payload->product->title,
            'short_description' => $payload->product->short_description,
            'sku'               => $payload->product->default_sku,
            'price'             => $payload->product->default_price,
            'quantity'          => $payload->quantity,
            'sub_total'         => $subtotal,
            'discount_total'    => $discountTotal,
            'total'             => $total,
        ]);

        return $orderItem;
    }
}
