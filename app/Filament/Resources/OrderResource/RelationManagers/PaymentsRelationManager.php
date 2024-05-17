<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Amountables\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Livewire\Component;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $label = 'payment';

    protected static ?string $pluralLabel = 'payments';

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
                                       ->default(Payment::class),
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
                                         ->numeric(thousandsSeparator: '.')
                                         ->summarize([
                                             Sum::make()
                                                ->formatStateUsing(function ($state) {
                                                    return 'Rp'.number_format($state, 0, ',', '.');
                                                }),
                                         ]),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                                           ->disabled(function (Component $livewire) {
                                               $order = $livewire->ownerRecord;

                                               return ! $order->status->canEditOrder();
                                           })
                                           ->after(function (Component $livewire) {
                                               $livewire->dispatch('refreshOrders', fields: [
                                                   'paid_total', 'grand_total',
                                               ]);
                                           }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                                         ->after(function (Component $livewire) {
                                             $livewire->dispatch('refreshOrders', fields: [
                                                 'paid_total', 'grand_total',
                                             ]);
                                         }),
                Tables\Actions\DeleteAction::make()
                                           ->after(function (Component $livewire) {
                                               $livewire->dispatch('refreshOrders', fields: [
                                                   'paid_total', 'grand_total',
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
