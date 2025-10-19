<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KraWeightResource\Pages;
use App\Filament\Resources\KraWeightResource\RelationManagers;
use App\Models\KraWeight;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class KraWeightResource extends Resource
{
    protected static ?string $model = KraWeight::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

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
                Select::make('faculty_rank_id')
                    ->relationship('facultyRank', 'name')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('instruction_weight')
                    ->numeric()->required()->minValue(0)->maxValue(100),
                TextInput::make('research_weight')
                    ->numeric()->required()->minValue(0)->maxValue(100),
                TextInput::make('extension_weight')
                    ->numeric()->required()->minValue(0)->maxValue(100),
                TextInput::make('professional_development_weight')
                    ->numeric()->required()->minValue(0)->maxValue(100)
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
            'index' => Pages\ListKraWeights::route('/'),
            'create' => Pages\CreateKraWeight::route('/create'),
            'edit' => Pages\EditKraWeight::route('/{record}/edit'),
        ];
    }
}
