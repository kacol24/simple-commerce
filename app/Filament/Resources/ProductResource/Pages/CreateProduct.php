<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        \DB::beginTransaction();
        $product = Product::create($data);

        $variant = $product->variants()->create([
            'sku' => $data['default_sku'],
        ]);

        $variant->prices()->create([
            'price'      => $data['default_price'],
            'cost_price' => $data['default_cost_price'],
        ]);
        \DB::commit();

        return $product;
    }
}
