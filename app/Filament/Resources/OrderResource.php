<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('channel_id')
                                       ->required()
                                       ->relationship('channel', 'name'),
                Forms\Components\Select::make('customer_id')
                                       ->label('Customer')
                                       ->required()
                                       ->native(false)
                                       ->relationship(
                                           name: 'customer',
                                           titleAttribute: 'name',
                                           modifyQueryUsing: fn(Builder $query) => $query->active()
                                       )
                                       ->searchable(['name', 'phone'])
                                       ->getOptionLabelFromRecordUsing(function (Model $customer) {
                                           return '['.$customer->phone.'] '.$customer->name;
                                       })
                                       ->preload(),
                Forms\Components\Select::make('reseller_id')
                                       ->label('Reseller')
                                       ->relationship(
                                           name: 'reseller',
                                           titleAttribute: 'name',
                                       )
                                       ->searchable(['name', 'phone'])
                                       ->getOptionLabelFromRecordUsing(function (Model $customer) {
                                           return '['.$customer->phone.'] '.$customer->name;
                                       })
                                       ->preload(),
                Forms\Components\Textarea::make('notes')
                                         ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('channel_id')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('customer_id')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('reseller_id')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('order_no')
                                         ->searchable(),
                Tables\Columns\TextColumn::make('sub_total')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('discount_total')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('fees_total')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('grand_total')
                                         ->numeric()
                                         ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                                         ->dateTime()
                                         ->sortable()
                                         ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ManageOrders::route('/'),
            //'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
