<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionCycleResource\Pages;
use App\Filament\Resources\PromotionCycleResource\RelationManagers;
use App\Models\PromotionCycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Select;
use Carbon\Carbon;

class PromotionCycleResource extends Resource
{
    protected static ?string $model = PromotionCycle::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'System Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Cycle Name')
                    ->required()
                    ->disabled(),
                Toggle::make('is_active')
                    ->label('Active for Selection')
                    ->default(true)
                    ->helperText('Instructors can only select active cycles when starting an application.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Remove bulk delete to prevent accidental data loss
            ])
            ->headerActions([
                // 
            ])
            ->defaultSort('name', 'desc');
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
            'index' => Pages\ListPromotionCycles::route('/'),
            'edit' => Pages\EditPromotionCycle::route('/{record}/edit'),
        ];
    }
}
