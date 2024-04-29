<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                     ->schema([
                         Section::make('Order Items')
                                ->schema([]),
                         Group::make()
                              ->schema([
                                  Section::make('Shipping')
                                         ->schema([])
                                         ->columnSpan(1),
                                  Section::make('Discounts')
                                         ->columnSpan(1),
                                  Section::make('Fees')
                                         ->columnSpan(1),
                              ])
                              ->columns(3),
                     ])
                     ->columnSpan(['lg' => 2]),
                Group::make()
                     ->schema([
                         Section::make('Order Details')
                                ->schema([
                                    Select::make('status')
                                          ->label('Status')
                                          ->relationship('channel', 'name')
                                          ->columnSpan(2),
                                    Select::make('channel_id')
                                          ->required()
                                          ->relationship('channel', 'name'),
                                    Placeholder::make('created_at')
                                               ->label('Placed At')
                                               ->content(function ($record) {
                                                   return $record->created_at;
                                               }),
                                    Select::make('customer_id')
                                          ->label('Customer')
                                          ->required()
                                          ->native(false)
                                          ->relationship(
                                              name: 'customer',
                                              titleAttribute: 'name',
                                              modifyQueryUsing: fn(Builder $query) => $query->active()
                                          )
                                          ->searchable(['name', 'phone'])
                                          ->preload()
                                          ->columnSpan(2)
                                          ->hint(function (Order $order) {
                                              return new HtmlString(
                                                  '<a target="_blank" href="'.$order->customer->whatsapp_url.'">+62 '.$order->customer->phone.'</a>'
                                              );
                                          })->columnSpan(1),
                                    Select::make('reseller_id')
                                          ->label('Reseller')
                                          ->relationship(
                                              name: 'reseller',
                                              titleAttribute: 'name',
                                          )
                                          ->searchable(['name', 'phone'])
                                          ->preload()
                                          ->different('customer_id')
                                          ->columnSpan(2)
                                          ->hint(function (Order $order) {
                                              if (! $order->reseller) {
                                                  return false;
                                              }

                                              return new HtmlString(
                                                  '<a target="_blank" href="'.$order->reseller->whatsapp_url.'">+62 '.$order->reseller->phone.'</a>'
                                              );
                                          })->columnSpan(1),
                                    RichEditor::make('notes')
                                              ->columnSpan(2),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->collapsed(),
                         Section::make('Order Summary'),
                         Section::make('Payments'),
                         Section::make('Activity Log')
                                ->collapsible()
                                ->collapsed(),
                     ])
                     ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }
}
