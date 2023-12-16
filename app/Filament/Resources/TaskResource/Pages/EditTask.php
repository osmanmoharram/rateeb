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
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (?Model $record) => $record->assigner?->id === auth()->id()),
        ];
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
                        ->visible(fn (?Model $record) => $record?->assigner?->id === auth()->user()->id),
                ]),
                Grid::make(1)->schema([
                    Textarea::make('description')
                        ->label('الوصف')
                        ->string()
                        ->visible(fn (?Model $record) => $record?->assigner?->id === auth()->user()->id),
                ]),
                Grid::make(2)->schema([
                    DatePicker::make('start_date')
                        ->label('تاريخ البداية')
                        ->required()
                        ->date()
                        ->visible(fn (?Model $record) => $record?->assigner?->id === auth()->user()->id),

                    DatePicker::make('end_date')
                        ->label('تاريخ النهاية')
                        ->required()
                        ->date()
                        ->after('start_date')
                        ->visible(fn (?Model $record) => $record?->assigner?->id === auth()->user()->id),
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
                    ])
                    ->visible(fn (?Model $record) => $record?->assigner?->id === auth()->user()->id),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'أنشأت' => 'أنشأت',
                        'بدأت' => 'بدأت',
                        'جاري العمل عليها' => 'جاري العمل عليها',
                        'منجزة' => 'منجزة',
                        'معلقة' => 'معلقة',
                        'ملغية' => 'ملغية'
                    ]),
                DatePicker::make('delivery_date')
                    ->label('تاريخ التسليم')
                    ->date()
                    ->after('start_date')
            ]);
    }
}
