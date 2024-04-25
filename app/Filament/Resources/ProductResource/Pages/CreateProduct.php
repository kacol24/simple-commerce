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
        $product = Product::create([
            'is_active'         => $data['is_active'],
            'title'             => $data['title'],
            'short_description' => $data['short_description'],
            'long_description'  => $data['long_description'],
        ]);

        $variant = $product->variants()->create([
            'sku' => $data['default_sku'],
        ]);

        $variant->prices()->create([
            'price' => $data['default_price'],
        ]);
        \DB::commit();

        return $product;
    }
}
