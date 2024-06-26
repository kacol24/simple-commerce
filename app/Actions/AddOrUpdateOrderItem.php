<?php

namespace App\Actions;

use App\DataObjects\AddOrUpdateOrderItemPayload;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class AddOrUpdateOrderItem extends AbstractAction
{
    public function execute(Model $order, AddOrUpdateOrderItemPayload $payload)
    {
        $subtotal = $payload->product->default_price * $payload->quantity;
        $discountTotal = $payload->discount;
        $total = $subtotal - $discountTotal;

        $existing = OrderItem::where([
            'order_id'         => $order->id,
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => $payload->product->id,
            'option'           => json_encode($payload->options),
            'notes'            => $payload->notes,
        ])->first();

        if ($existing) {
            $payload->quantity += $existing->quantity;
        }

        $orderItem = $order->items()->updateOrCreate([
            'purchasable_type' => ProductVariant::class,
            'purchasable_id'   => $payload->product->id,
            'option'           => $payload->options,
            'notes'            => $payload->notes,
        ], [
            'title'             => $payload->product->title,
            'short_description' => $payload->product->short_description,
            'sku'               => $payload->product->default_sku,
            'price'             => $payload->product->default_price,
            'cost_price'        => $payload->product->default_cost_price,
            'quantity'          => $payload->quantity,
            'sub_total'         => $subtotal,
            'discount_total'    => $discountTotal,
            'total'             => $total,
        ]);

        return $orderItem;
    }
}
