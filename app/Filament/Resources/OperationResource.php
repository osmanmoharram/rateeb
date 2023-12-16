<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperationResource\Pages;
use App\Filament\Resources\OperationResource\RelationManagers;
use App\Models\Operation;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OperationResource extends Resource
{
    protected static ?string $model = Operation::class;

    protected static ?string $modelLabel = 'معاملة';

    protected static ?string $pluralModelLabel = 'المعاملات';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('number')
                        ->required()
                        ->string()
                        // ->unique(Operation::class, 'number', fn (?Model $record) => $record?->number, 'number')
                        ->label('الرقم'),

                    TextInput::make('name')
                        ->required()
                        ->string()
                        ->minValue(3)
                        ->label('الإسم'),

                    Select::make('type')
                        ->options([
                            'outgoing' => 'صادرة',
                            'incoming' => 'واردة'
                        ])
                        ->default('incoming')
                        ->required()
                        ->in(['incoming', 'outgoing'])
                        ->label('النوع')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('الرقم'),
                TextColumn::make('name')->label('الإسم'),
                TextColumn::make('type')->label('النوع')
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
            'index' => Pages\ListOperations::route('/'),
            'create' => Pages\CreateOperation::route('/create'),
            'edit' => Pages\EditOperation::route('/{record}/edit'),
        ];
    }
}
