<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalariesResource\Pages;
use App\Filament\Resources\SalariesResource\RelationManagers;
use App\Models\Salaries;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalariesResource extends Resource
{
    protected static ?string $model = Salaries::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = -3;

    public static function getPluralModelLabel(): string
    {
        return __('dash.salaries');
    }
    public static function getNavigationLabel(): string
    {
        return __('dash.salaries');
    }


    public static function getNavigationGroup(): ?string
    {
        return __('dash.employees_salaries');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListSalaries::route('/'),
            'create' => Pages\CreateSalaries::route('/create'),
            'edit' => Pages\EditSalaries::route('/{record}/edit'),
        ];
    }
}
