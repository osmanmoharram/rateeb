<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigner_id'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->string()
                        ->minValue(3)
                ]),
                Grid::make(1)->schema([
                    Textarea::make('description')
                        ->label('الوصف')
                        ->string(),
                ]),
                Grid::make(2)->schema([
                    DatePicker::make('start_date')
                        ->label('تاريخ البداية')
                        ->required()
                        ->date(),

                    DatePicker::make('end_date')
                        ->label('تاريخ النهاية')
                        ->required()
                        ->date()
                        ->after('start_date'),
                ]),
                Grid::make(2)->schema([
                    Select::make('assignee_id')
                        ->label('تسند إلى')
                        ->exists(table: 'users', column: 'id')
                        ->relationship(
                            name: 'assignee',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->when(auth()->user()->job_id !== null, fn ($query) => $query
                                    ->where('manager_id', auth()->id())
                                    ->orWhereIn('manager_id', auth()->user()->employees()->select('id')->get()->toArray()),
                                    fn ($query) => $query->whereNotNull('job_id')
                                )
                        )
                    ]),
            ]);
    }
}
