<?php

namespace App\DataObjects;

use App\Models\Product;

class AddOrUpdateOrderItemPayload
{
    public function __construct(
        public Product $product,
        public $quantity,
        public $discount = 0,
    ) {
    }

    public static function fromFilamentAction($data)
    {
        $product = Product::find($data['product_id']);

        return new self(
            $product,
            $data['quantity'],
            $data['discount_total'] ?? 0
        );
    }
}
