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
use App\Forms\Components\TrimmedIntegerInput;
use App\Forms\Components\TrimmedNumericInput;
use App\Tables\Columns\ScoreColumn;

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
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Overall Average Rating')
                    ->numeric(2, '.', ',')
                    ->state(function (Submission $record): float {
                        $data = $record->data;
                        $prefix = 'client';

                        $keys = [];
                        for ($year = 1; $year <= 4; $year++) {
                            for ($sem = 1; $sem <= 2; $sem++) {
                                $keys[] = "{$prefix}_ay{$year}_sem{$sem}";
                            }
                        }

                        $ratings = [];
                        foreach ($keys as $key) {
                            if (isset($data[$key]) && is_numeric($data[$key])) {
                                $ratings[] = min((float)$data[$key], 100.0);
                            } else {
                                $ratings[] = 0.0;
                            }
                        }

                        $sum = array_sum($ratings);
                        if ($sum === 0.0) return 0.0;

                        $totalSemesters = count($keys);
                        $deductedSemesters = (int)($data["{$prefix}_deducted_semesters"] ?? 0);
                        $reason = $data["{$prefix}_deduction_reason"] ?? 'NOT APPLICABLE';
                        $isValidDeduction = $reason !== 'NOT APPLICABLE' && $reason !== 'SELECT OPTION';

                        $divisor = $totalSemesters;
                        if ($isValidDeduction && $deductedSemesters > 0 && $deductedSemesters < $totalSemesters) {
                            $divisor = $totalSemesters - $deductedSemesters;
                        }
                        $divisor = max(1, $divisor);

                        return $sum / $divisor;
                    }),

                ScoreColumn::make('score'),
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
                    TrimmedNumericInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem1")
                        ->label('1st Semester Rating')
                        ->step('0.01')
                        ->rules(['numeric', 'between:0,100'])
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                    TrimmedNumericInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem2")
                        ->label('2nd Semester Rating')
                        ->step('0.01')
                        ->rules(['numeric', 'between:0,100'])
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
                        ->searchable()
                        ->required()
                        ->live(),

                    TrimmedIntegerInput::make($deductedSemestersKey)
                        ->label('Number of Semesters to Deduct')
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
