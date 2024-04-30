<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Amountables\Discount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Component;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    protected static ?string $label = 'discount';

    protected static ?string $pluralLabel = 'discounts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                                          ->required()
                                          ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                                          ->numeric()
                                          ->prefix('Rp')
                                          ->minValue(1)
                                          ->default(0)
                                          ->required(),
                Forms\Components\TextInput::make('description'),
                Forms\Components\Hidden::make('amountable_type')
                                       ->default(Discount::class),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('amount')
                                         ->prefix('Rp')
                                         ->numeric(thousandsSeparator: '.'),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                                           ->after(function (Component $livewire) {
                                               $livewire->dispatch('refreshOrders', fields: [
                                                   'discount_total', 'grand_total',
                                               ]);
                                           }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                                         ->after(function (Component $livewire) {
                                             $livewire->dispatch('refreshOrders', fields: [
                                                 'discount_total', 'grand_total',
                                             ]);
                                         }),
                Tables\Actions\DeleteAction::make()
                                           ->after(function (Component $livewire) {
                                               $livewire->dispatch('refreshOrders', fields: [
                                                   'discount_total', 'grand_total',
                                               ]);
                                           }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated(false);
    }
}
