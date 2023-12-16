<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        if (auth()->user()->job?->title == 'موظف') {
            return [];
        }
        
        return [
            'الكل' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query
                ->whereBelongsTo(auth()->user(), 'assigner')
                ->orWhereBelongsTo(auth()->user(), 'assignee')
            ),
            'المسندة إلي' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereBelongsTo(auth()->user(), 'assignee')),
            'التي قمت بإسنادها' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereBelongsTo(auth()->user(), 'assigner'))
        ];
    }
}
