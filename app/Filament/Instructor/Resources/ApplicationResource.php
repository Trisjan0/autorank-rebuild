<?php

namespace App\Filament\Instructor\Resources;

use App\Filament\Instructor\Resources\ApplicationResource\Pages;
use App\Filament\Instructor\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Tables\Columns\ScoreColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use App\Models\PromotionCycle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function canCreate(): bool
    {
        // Checks if the logged-in user has a rank assigned.
        return !is_null(Auth::user()->faculty_rank_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('evaluation_cycle')
                    ->label('Promotion Cycle')
                    ->options(
                        PromotionCycle::where('is_active', true)->pluck('name', 'name')
                    )
                    ->searchable()
                    ->required(),
                Textarea::make('remarks')
                    ->label('Evaluator Remarks')
                    ->columnSpanFull()
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('evaluation_cycle')
                    ->label('Cycle')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'Pending Validation' => 'warning',
                        'validated' => 'success',
                        'rejected' => 'danger',
                        default => 'primary',
                    }),
                ScoreColumn::make('final_score')
                    ->label('Final Score'),
                Tables\Columns\TextColumn::make('highest_attainable_rank')
                    ->label('Result')
                    ->badge()
                    ->placeholder('Not yet validated.'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        // Only allow editing if it's still a draft
                        ->visible(fn(Application $record): bool => $record->status === 'draft'),
                    DeleteAction::make()
                        // Only allow deleting if it's still a draft
                        ->visible(fn(Application $record): bool => $record->status === 'draft'),
                ])
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'view' => Pages\ViewApplication::route('/{record}'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }
}
