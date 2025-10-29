<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class QualityOfExtensionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function submissionExistsForCurrentType(): bool
    {
        $activeApplicationId = Auth::user()?->activeApplication?->id;
        if (!$activeApplicationId) {
            return false;  // temporarily allow no application id submission
        }

        return Submission::where('user_id', Auth::id())
            ->where('application_id', $activeApplicationId)
            ->where('type', 'extension-quality-rating')
            ->exists();
    }

    protected function getCurrentSubmissionId(): ?int
    {
        $activeApplicationId = Auth::user()?->activeApplication?->id;
        if (!$activeApplicationId) {
            return null;
        }

        return Submission::where('user_id', Auth::id())
            ->where('application_id', $activeApplicationId)
            ->where('type', 'extension-quality-rating')
            ->value('id');
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA III')
                    ->where('type', 'extension-quality-rating')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Client Satisfaction Rating Submission')
            ->columns([
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-quality-rating';
                        return $data;
                    })
                    ->modalHeading('Submit Client Satisfaction Ratings')
                    ->modalWidth('4xl')
                    ->hidden(fn(): bool => $this->submissionExistsForCurrentType()),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit Rating Data')
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Client Satisfaction Ratings')
                    ->modalWidth('4xl'),
            ]);
    }

    private function getRatingFields(): array
    {
        $prefix = 'client';
        $fields = [];
        $currentYear = (int) date('Y');

        for ($yearIndex = 0; $yearIndex < 4; $yearIndex++) {
            $startYear = $currentYear - ($yearIndex + 1);
            $endYear = $startYear + 1;
            $ayLabel = "AY {$startYear}-{$endYear}";
            $yearKeySuffix = 4 - $yearIndex;

            $fields[] = Section::make($ayLabel)
                ->schema([
                    TextInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem1")
                        ->label('1st Semester Rating')
                        ->type('number')
                        ->step('0.01')
                        ->rules(['numeric', 'between:0,100'])
                        ->extraInputAttributes([
                            'onkeydown' => "return !['e','E','+','-'].includes(event.key);",
                        ])
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                    TextInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem2")
                        ->label('2nd Semester Rating')
                        ->type('number')
                        ->step('0.01')
                        ->rules(['numeric', 'between:0,100'])
                        ->extraInputAttributes([
                            'onkeydown' => "return !['e','E','+','-'].includes(event.key);",
                        ])
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                ])->columns(2);
        }
        return array_reverse($fields);
    }


    protected function getFormSchema(): array
    {
        $prefix = 'client';
        $deductedSemestersKey = "data.{$prefix}_deducted_semesters";
        $reasonKey = "data.{$prefix}_deduction_reason";

        return [
            Section::make('Client Satisfaction Ratings')
                ->description('Enter the average client satisfaction rating received per semester for the last 4 academic years. Rating scale: 0-100.')
                ->schema($this->getRatingFields())
                ->columns(2),

            Section::make('Leave / Deduction Information (If Applicable)')
                ->schema([
                    Select::make($reasonKey)
                        ->label('Reason for Deducting Semesters (Leave)')
                        ->options([
                            'NOT APPLICABLE' => 'Not Applicable',
                            'ON APPROVED STUDY LEAVE' => 'On Approved Study Leave',
                            'ON APPROVED SABBATICAL LEAVE' => 'On Approved Sabbatical Leave',
                            'ON APPROVED MATERNITY LEAVE' => 'On Approved Maternity Leave',
                        ])
                        ->default('NOT APPLICABLE')
                        ->required()
                        ->live(),

                    TextInput::make($deductedSemestersKey)
                        ->label('Number of Semesters to Deduct')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(7)
                        ->default(0)
                        ->required()
                        ->visible(fn(Get $get): bool => $get($reasonKey) !== 'NOT APPLICABLE'),
                ])->columns(2),

            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Consolidated Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-quality-rating')
                ->columnSpanFull(),
        ];
    }
}
