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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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

    /**
     * Define the Infolist for the ViewApplication page.
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Application Details')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('evaluation_cycle')
                            ->label('Promotion Cycle'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'draft' => 'gray',
                                'Pending Validation' => 'warning',
                                'validated' => 'success',
                                'rejected' => 'danger',
                                default => 'primary',
                            }),
                        Infolists\Components\TextEntry::make('final_score')
                            ->label('Final Score')
                            ->placeholder('Not yet scored.'),
                        Infolists\Components\TextEntry::make('highest_attainable_rank')
                            ->label('Result')
                            ->badge()
                            ->placeholder('Not yet validated.'),
                        Infolists\Components\TextEntry::make('applicant_current_rank')
                            ->label('Rank at Time of Submission'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime('M j, Y g:ia'),
                        Infolists\Components\TextEntry::make('remarks')
                            ->label('Evaluator Remarks')
                            ->columnSpanFull()
                            ->placeholder('No remarks yet.'),
                    ]),

                // Helper function to create a repeatable entry for submissions
                $fn = function (string $kraLabel, array $submissionTypes) use ($infolist) {
                    return Infolists\Components\Section::make($kraLabel)
                        ->collapsible()
                        ->schema([
                            Infolists\Components\RepeatableEntry::make('submissions')
                                ->label(false)
                                ->hidden(fn(Application $record) => $record->submissions->whereIn('type', $submissionTypes)->isEmpty())
                                ->record($infolist->getRecord()->submissions->whereIn('type', $submissionTypes))
                                ->schema([
                                    Infolists\Components\TextEntry::make('id')
                                        ->label(false)
                                        ->formatStateUsing(function ($state, $record) {
                                            $title = $record->data['title'] ?? $record->data['name'] ?? $record->data['activity_title'] ?? 'Untitled';
                                            $type = Str::of($record->type)->replace('-', ' ')->title();
                                            return new HtmlString("<div>{$title}</div><div class='text-xs text-gray-500'>{$type}</div>");
                                        }),
                                ]),
                            Infolists\Components\TextEntry::make($kraLabel . '_empty')
                                ->label(false)
                                ->value('No submissions for this KRA.')
                                ->color('gray')
                                ->hidden(fn(Application $record) => $record->submissions->whereIn('type', $submissionTypes)->isNotEmpty())
                        ]);
                },

                // KRA I
                $fn('KRA I: Instruction', [
                    'te-student-evaluation',
                    'te-supervisor-evaluation',
                    'im-sole-authorship',
                    'im-co-authorship',
                    'im-academic-program',
                    'mentorship-adviser',
                    'mentorship-panel',
                    'mentorship-mentor'
                ]),

                // KRA II
                $fn('KRA II: Research & Innovation', [
                    'research-sole-authorship',
                    'research-co-authorship',
                    'research-translated-lead',
                    'research-translated-contributor',
                    'research-citation-local',
                    'research-citation-international',
                    'invention-patent-sole',
                    'invention-patent-co-inventor',
                    'invention-utility-design-sole',
                    'invention-utility-design-co-inventor',
                    'invention-commercialized-local',
                    'invention-commercialized-international',
                    'invention-software-new-sole',
                    'invention-software-new-co',
                    'invention-software-updated',
                    'invention-plant-animal-sole',
                    'invention-plant-animal-co',
                    'creative-performing-art',
                    'creative-exhibition',
                    'creative-juried-design',
                    'creative-literary-publication'
                ]),

                // KRA III
                $fn('KRA III: Extension', [
                    'extension-linkage',
                    'extension-income-generation',
                    'accreditation_services',
                    'judge_examiner',
                    'consultant',
                    'media_service',
                    'training_resource_person',
                    'social_responsibility',
                    'extension-quality-rating',
                    'extension-bonus-designation'
                ]),

                // KRA IV
                $fn('KRA IV: Professional Development', [
                    'profdev-organization',
                    'profdev-doctorate',
                    'profdev-additional-degree',
                    'profdev-conference-training',
                    'profdev-paper-presentation',
                    'profdev-award-recognition',
                    'profdev-academic-service',
                    'profdev-industry-experience'
                ]),
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
            // could make a submissionRelationManager
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
