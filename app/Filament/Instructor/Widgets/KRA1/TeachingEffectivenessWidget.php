<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Forms\Components\TrimmedIntegerInput;
use App\Forms\Components\TrimmedNumericInput;
use App\Tables\Columns\ScoreColumn;
use App\Filament\Instructor\Widgets\BaseKRAWidget;

class TeachingEffectivenessWidget extends BaseKRAWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a1.teaching-effectiveness-widget';

    public ?string $activeTable = 'student_evaluation';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
    }

    protected function getKACategory(): string
    {
        return 'KRA I';
    }

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'student_evaluation'
            ? 'te-student-evaluation'
            : 'te-supervisor-evaluation';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading(fn(): string => $this->getTableHeading())
            ->columns($this->getTableColumns())
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions())
            ->checkIfRecordIsSelectableUsing(
                fn(Submission $record): bool => !$this->submissionExistsForCurrentType() || $record->id === $this->getCurrentSubmissionId()
            );
    }

    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType())
            ->where('application_id', $this->selectedApplicationId);
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Last Updated')
                ->dateTime('M j, Y g:ia')
                ->sortable(),

            Tables\Columns\TextColumn::make('average_rating')
                ->label('Overall Average Rating')
                ->numeric(2, '.', ',')
                ->state(function (Submission $record): float {
                    $data = $record->data;
                    $prefix = $this->activeTable === 'student_evaluation' ? 'student' : 'supervisor';

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
                    $reason = $data['reason_for_deducting'] ?? 'NOT APPLICABLE';
                    $isValidDeduction = $reason !== 'NOT APPLICABLE' && $reason !== 'SELECT OPTION';

                    $divisor = $totalSemesters;
                    if ($isValidDeduction && $deductedSemesters > 0 && $deductedSemesters < $totalSemesters) {
                        $divisor = $totalSemesters - $deductedSemesters;
                    }
                    $divisor = max(1, $divisor);

                    return $sum / $divisor;
                }),

            ScoreColumn::make('score'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = $this->selectedApplicationId;
                    $data['category'] = $this->getKACategory();
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading('Submit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('4xl')
                ->hidden(fn(): bool => $this->submissionExistsForCurrentType())
                ->after(fn() => $this->mount()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Evaluation Data')
                ->form($this->getFormSchema())
                ->modalHeading('Edit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('4xl')
                ->visible($this->getActionVisibility()),
            DeleteAction::make()
                ->after(fn() => $this->mount())
                ->visible($this->getActionVisibility()),
        ];
    }

    private function getRatingFields(): array
    {
        $prefix = $this->activeTable === 'student_evaluation' ? 'student' : 'supervisor';
        $fields = [];
        $currentYear = (int) date('Y');

        for ($yearIndex = 0; $yearIndex < 4; $yearIndex++) {
            $startYear = $currentYear - ($yearIndex + 1);
            $endYear = $startYear + 1;
            $ayLabel = "AY {$startYear}–{$endYear}";
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
        $prefix = $this->activeTable === 'student_evaluation' ? 'student' : 'supervisor';
        $deductedSemestersKey = "data.{$prefix}_deducted_semesters";

        return [
            Section::make('Evaluation Ratings')
                ->description('Enter the average rating received per semester for the last 4 academic years.')
                ->schema($this->getRatingFields())
                ->columns(2),

            Section::make('Leave / Deduction Information (If Applicable)')
                ->schema([
                    Select::make('data.reason_for_deducting')
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

                    TrimmedIntegerInput::make($deductedSemestersKey)
                        ->label('Number of Semesters to Deduct')
                        ->minValue(0)
                        ->maxValue(7)
                        ->default(0)
                        ->required()
                        ->visible(fn(Get $get): bool => $get('data.reason_for_deducting') !== 'NOT APPLICABLE'),
                ])->columns(2),

            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory(fn(): string => 'proof-documents/kra1-te/' . $this->activeTable)
                ->columnSpanFull(),
        ];
    }
}
