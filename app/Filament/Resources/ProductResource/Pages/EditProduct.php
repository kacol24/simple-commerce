<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        \DB::beginTransaction();
        $record->update($data);

        $record->defaultVariant()->update([
            'sku' => $data['default_sku'],
        ]);

        $record->defaultVariant()->basePrices()->first()->update([
            'price'      => $data['default_price'],
            'cost_price' => $data['default_cost_price'] ?? null,
        ]);
        \DB::commit();

        return $record;
    }
}
