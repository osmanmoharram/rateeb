<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $modelLabel = 'مهمة';

    protected static ?string $pluralModelLabel = 'المهام';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    TextInput::make('title')
                        ->label('العنوان')
                        ->required()
                        ->string()
                        ->minValue(3),
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
                                ->where('manager_id', auth()->id())
                                ->orWhereIn('manager_id', auth()->user()->employees()->select('id')->get()->toArray())
                        )
                    ]),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'أنشأت' => 'أنشأت',
                        'بدأت' => 'بدأت',
                        'جاري العمل عليها' => 'جاري العمل عليها',
                        'منجزة' => 'منجزة',
                        'معلقة' => 'معلقة',
                        'ملغية' => 'ملغية'
                    ])
                    ->hiddenOn(Pages\CreateTask::class),

                DatePicker::make('delivery_date')
                    ->label('تاريخ التسليم')
                    ->date()
                    ->after('start_date')
                    ->hiddenOn(Pages\CreateTask::class)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->description(fn (Task $record): string => $record->description)
                    ->limit(50)
                    ->label('العنوان'),
                    
                TextColumn::make('assignee.name')
                    ->label('الموظف المسندة إليه'),

                TextColumn::make('start_date')
                    ->label('تاريخ البداية'),

                TextColumn::make('end_date')
                    ->label('تاريخ النهاية'),

                TextColumn::make('delivery_date')->default('-')
                    ->label('تاريخ التسليم'),

                SelectColumn::make('status')
                    ->options([
                        'جاري العمل عليها' => 'جاري العمل عليها',
                        'أنجزت' => 'أنجزت',
                        'أنجزت متأخرة' => 'أنجزت متأخرة'
                    ])
                    ->afterStateUpdated(function (Task $record, string $state) {
                        if ($state == 'أنجزت') {
                            $record->delivery_date = today()->toDateString();

                            if (Carbon::parse($record->delivery_date)->isAfter(Carbon::parse($record->end_date))) {
                                $record->status = 'أنجزت متأخرة';
                            }

                            $record->save();
                        }
                    })
                    ->label('الحالة'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereBelongsTo(auth()->user(), 'assigner')
                ->orWhereBelongsTo(auth()->user(), 'assignee')
            );
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
