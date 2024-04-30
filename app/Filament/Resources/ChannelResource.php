<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_default')
                                       ->label('Default')
                                       ->required(),
                Group::make()
                     ->schema([
                         Forms\Components\TextInput::make('name')
                                                   ->required()
                                                   ->maxLength(255),
                         Forms\Components\TextInput::make('url')
                                                   ->label('URL')
                                                   ->maxLength(255),
                     ])
                     ->columns(2),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_default')
                                         ->label('Default')
                                         ->boolean(),
                Tables\Columns\TextColumn::make('name')
                                         ->searchable(),
                Tables\Columns\TextColumn::make('url')
                                         ->label('URL')
                                         ->toggleable(isToggledHiddenByDefault: true)
                                         ->searchable(),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                    //Tables\Actions\ForceDeleteBulkAction::make(),
                    //Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChannels::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
                     ->withoutGlobalScopes([
                         SoftDeletingScope::class,
                     ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return 'Soon';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
