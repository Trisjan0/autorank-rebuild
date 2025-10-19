<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacultyRankResource\Pages;
use App\Filament\Resources\FacultyRankResource\RelationManagers;
use App\Models\FacultyRank;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FacultyRankResource extends Resource
{
    protected static ?string $model = FacultyRank::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'System Management';

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole(['Admin', 'Super Admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('level')
                    ->required()
                    ->integer()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('level', 'asc')
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
            'index' => Pages\ListFacultyRanks::route('/'),
            'create' => Pages\CreateFacultyRank::route('/create'),
            'edit' => Pages\EditFacultyRank::route('/{record}/edit'),
        ];
    }
}
