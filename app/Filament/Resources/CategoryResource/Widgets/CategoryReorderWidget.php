<?php

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use InvadersXX\FilamentNestedList\Actions\Action;
use InvadersXX\FilamentNestedList\Actions\ActionGroup;
use InvadersXX\FilamentNestedList\Actions\DeleteAction;
use InvadersXX\FilamentNestedList\Actions\EditAction;
use InvadersXX\FilamentNestedList\Actions\ViewAction;
use InvadersXX\FilamentNestedList\Widgets\NestedList as BaseWidget;

class CategoryReorderWidget extends BaseWidget
{
    protected static string $model = Category::class;

    protected static int $maxDepth = 2;

    protected ?string $treeTitle = 'CategoryReorderWidget';

    protected bool $enableTreeTitle = true;

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('title'),
        ];
    }

    // INFOLIST, CAN DELETE
    public function getViewFormSchema(): array
    {
        return [
            //
        ];
    }

    // CUSTOMIZE ICON OF EACH RECORD, CAN DELETE
    // public function getTreeRecordIcon(?\Illuminate\Database\Eloquent\Model $record = null): ?string
    // {
    //     return null;
    // }

    // CUSTOMIZE ACTION OF EACH RECORD, CAN DELETE
    protected function getTreeActions(): array
    {
        return [
            Action::make('helloWorld')
                  ->action(function () {
                      Notification::make()->success()->title('Hello World')->send();
                  }),
             ViewAction::make(),
             EditAction::make(),
            ActionGroup::make([

                ViewAction::make(),
                EditAction::make(),
            ]),
            DeleteAction::make(),
        ];
    }

    // OR OVERRIDE FOLLOWING METHODS
    protected function hasDeleteAction(): bool
    {
        return true;
    }

    protected function hasEditAction(): bool
    {
        return true;
    }
    //protected function hasViewAction(): bool
    //{
    //    return true;
    //}
}
