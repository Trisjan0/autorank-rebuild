<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TeachingEffectivenessWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a1.teaching-effectiveness-widget';

    public ?string $activeTable = 'student_evaluation';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
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

    /**
     * Check if a submission already exists for the active tab and application cycle.
     */
    protected function submissionExistsForCurrentType(): bool
    {
        $activeApplicationId = Auth::user()?->activeApplication?->id;
        if (!$activeApplicationId) {
            return false; // temporarily allow no application id submission
        }

        return Submission::where('user_id', Auth::id())
            ->where('application_id', $activeApplicationId)
            ->where('type', $this->getActiveSubmissionType())
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
            ->where('type', $this->getActiveSubmissionType())
            ->value('id');
    }

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'student_evaluation'
            ? 'te-student-evaluation'
            : 'te-supervisor-evaluation';
    }


    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType());
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
                ->dateTime()
                ->sortable(),
            Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
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
                    $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                    $data['category'] = 'KRA I';
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading('Submit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('4xl')
                ->hidden(fn(): bool => $this->submissionExistsForCurrentType()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Evaluation Data')
                ->form($this->getFormSchema())
                ->modalHeading('Edit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('4xl'),
        ];
    }

    /**
     * Helper to generate the 8 rating fields for the form.
     */
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
                    TextInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem1")
                        ->label('1st Semester Rating')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(100)
                        ->required(),
                    TextInput::make("data.{$prefix}_ay{$yearKeySuffix}_sem2")
                        ->label('2nd Semester Rating')
                        ->integer()
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

                    TextInput::make($deductedSemestersKey)
                        ->label('Number of Semesters to Deduct')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(7)
                        ->default(0)
                        ->required()
                        ->visible(fn(Get $get): bool => $get('data.reason_for_deducting') !== 'NOT APPLICABLE'),
                ])->columns(2),

            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Consolidated Evidence)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory(fn(): string => 'proof-documents/kra1-te/' . $this->activeTable)
                ->columnSpanFull(),
        ];
    }
}
