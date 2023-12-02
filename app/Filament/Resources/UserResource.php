<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Job;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Phpsa\FilamentPasswordReveal\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'موظف';

    protected static ?string $pluralModelLabel = 'الموظفين';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->string()
                ->minValue(3)
                ->label('الإسم')
                ->helperText('الرجاء إدخال الإسم الرباعي'),

            TextInput::make('email')
                ->required()
                ->label('البريد الإلكتروني'),

            Select::make('job_id')
                ->label('المسمى الوظيفي')
                ->relationship('job', 'title')
                ->required(),

            Select::make('manager_id')->relationship(
                name: 'manager',
                titleAttribute: 'name',
                modifyQueryUsing: fn (Builder $query) => $query->whereRelation('job', 'title', '=', 'مدير ')
            )
            ->label('المدير المباشر'),

            Password::make('password')
                ->required()
                ->confirmed()
                ->hiddenOn(Pages\EditUser::class)
                ->label('كلمة المرور'),

            Password::make('password_confirmation')
                ->required()
                ->hiddenOn(Pages\EditUser::class)
                ->label('تأكيد كلمة المرور'),
                 
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('الإسم'),

                TextColumn::make('email')->label('البريد الإلكتروني'),

                TextColumn::make('job.title')
                    ->searchable()
                    ->sortable()
                    ->label('المسمى الوظيفي'),

                TextColumn::make('manager.name')->label('المدير المباشر'),
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
                ->whereBelongsTo(auth()->user(), 'manager')
                ->orWhereIn('manager_id', User::whereBelongsTo(auth()->user(), 'manager')->select('id')->get()->all())
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
