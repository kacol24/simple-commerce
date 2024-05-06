<?php

namespace App\DataObjects;

use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Support\Arr;

class AddOrUpdateOrderItemPayload
{
    public function __construct(
        public Product $product,
        public $quantity,
        public $discount = 0,
        public $options = null,
        public $notes = null,
    ) {
    }

    public static function fromFilamentAction($data)
    {
        $product = Product::find($data['product_id']);
        $payloadOption = Arr::mapWithKeys($data['option'], function ($option){
            $id = $option['id'] ?? $option['key'];
            return [$id => $option['value']];
        });
        $options = ProductOption::query()
                                ->whereIn('id', array_keys($payloadOption))
                                ->get()
                                ->map(
                                    fn($option) => [
                                        'id'    => $option->id,
                                        'key'   => $option->display_name,
                                        'value' => $payloadOption[$option->id],
                                    ]
                                );

        return new self(
            $product,
            $data['quantity'],
            $data['discount_total'] ?? 0,
            $options->toArray(),
            $data['notes']
        );
    }
}
