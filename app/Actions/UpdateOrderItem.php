<?php

namespace App\Actions;

use App\DataObjects\AddOrUpdateOrderItemPayload;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;

class UpdateOrderItem extends AbstractAction
{
    public function execute(Model $orderItem, AddOrUpdateOrderItemPayload $payload)
    {
        $subtotal = $payload->product->default_price * $payload->quantity;
        $discountTotal = $payload->discount;
        $total = $subtotal - $discountTotal;

        $orderItem->update([
            'purchasable_type'  => ProductVariant::class,
            'purchasable_id'    => $payload->product->id,
            'option'            => $payload->options,
            'title'             => $payload->product->title,
            'short_description' => $payload->product->short_description,
            'sku'               => $payload->product->default_sku,
            'price'             => $payload->product->default_price,
            'cost_price'        => $payload->product->default_cost_price,
            'quantity'          => $payload->quantity,
            'sub_total'         => $subtotal,
            'discount_total'    => $discountTotal,
            'total'             => $total,
            'notes'             => $payload->notes,
        ]);

        return $orderItem->refresh();
    }
}
