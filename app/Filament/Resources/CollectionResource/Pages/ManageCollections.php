<?php

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionResource\Widgets\CollectionWidget;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCollections extends ManageRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CollectionWidget::class,
        ];
    }
}
