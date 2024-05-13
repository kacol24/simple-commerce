<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema())
            ->columns(1);
    }

    public static function getFormSchema()
    {
        return [
            Forms\Components\Toggle::make('is_active')
                                   ->required()
                                   ->label('Active?')
                                   ->default(true),
            Group::make()
                 ->schema([
                     Forms\Components\TextInput::make('name')
                                               ->required()
                                               ->maxLength(255),
                     Forms\Components\TextInput::make('phone')
                                               ->tel()
                                               ->maxLength(20)
                                               ->prefix('+62'),
                 ])
                 ->columns(2),
            Forms\Components\Repeater::make('addresses')
                                     ->relationship('addresses')
                                     ->reorderable(false)
                                     ->simple(
                                         Forms\Components\Textarea::make('address')
                                     ),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                                         ->searchable(),
                Tables\Columns\TextColumn::make('friendly_phone')
                                         ->label('Phone')
                                         ->searchable()
                                         ->prefix('+62 '),
                Tables\Columns\ToggleColumn::make('is_active')
                                           ->label('Active?'),
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
                Tables\Filters\TernaryFilter::make('is_active')
                                            ->label('Active?'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                                     ->label('WhatsApp')
                                     ->url(fn(Customer $record): string => $record->whatsapp_url)
                                     ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('Publish')
                              ->deselectRecordsAfterCompletion()
                              ->requiresConfirmation()
                              ->action(function (Collection $records) {
                                  Customer::whereIn('id', $records->pluck('id')->toArray())
                                          ->update([
                                              'is_active' => true,
                                          ]);
                              }),
                    BulkAction::make('Un-publish')
                              ->deselectRecordsAfterCompletion()
                              ->requiresConfirmation()
                              ->action(function (Collection $records) {
                                  Customer::whereIn('id', $records->pluck('id')->toArray())
                                          ->update([
                                              'is_active' => false,
                                          ]);
                              }),
                    //Tables\Actions\DeleteBulkAction::make(),
                    //Tables\Actions\ForceDeleteBulkAction::make(),
                    //Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
                     ->withoutGlobalScopes([
                         SoftDeletingScope::class,
                     ]);
    }
}
