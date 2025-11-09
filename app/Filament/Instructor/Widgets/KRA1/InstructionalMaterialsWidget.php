<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Forms\Get;
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;
use App\Filament\Instructor\Widgets\BaseKRAWidget;
use App\Filament\Traits\HandlesKRAFileUploads;

class InstructionalMaterialsWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a1.instructional-materials-widget';

    public ?string $activeTable = 'sole_authorship';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
    }

    /**
     * Provides the nested folder path to the GoogleDriveService
     */
    protected function getGoogleDriveFolderPath(): array
    {
        $kra = $this->getKACategory();

        switch ($this->activeTable) {
            case 'sole_authorship':
                return [$kra, 'B: Instructional Materials', 'Sole Authorship'];
            case 'co_authorship':
                return [$kra, 'B: Instructional Materials', 'Co-Authorship'];
            case 'academic_program':
                return [$kra, 'B: Academic Program Development'];
            default:
                return [$kra, Str::slug($this->getActiveSubmissionType())];
        }
    }

    protected function getKACategory(): string
    {
        return 'KRA I';
    }

    protected function getActiveSubmissionType(): string
    {
        $typeMap = [
            'sole_authorship' => 'im-sole-authorship',
            'co_authorship' => 'im-co-authorship',
            'academic_program' => 'im-academic-program',
        ];
        return $typeMap[$this->activeTable] ?? 'im-sole-authorship';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading(fn(): ?string => $this->getTableHeading())
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

    protected function getTableHeading(): ?string
    {
        return Str::of($this->activeTable)
            ->replace('_', ' ')
            ->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        return match ($this->activeTable) {
            'sole_authorship' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.material_type')
                    ->label('Material Type')
                    ->formatStateUsing(fn(?string $state) => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
                Tables\Columns\TextColumn::make('data.date_approved')->label('Date Approved')->date(),
                ScoreColumn::make('score'),
            ],
            'co_authorship' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.material_type')
                    ->label('Material Type')
                    ->formatStateUsing(fn(?string $state) => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
                Tables\Columns\TextColumn::make('data.date_approved')->label('Date Approved')->date(),
                Tables\Columns\TextColumn::make('data.contribution_percentage')->label('% Contribution')->suffix('%'),
                ScoreColumn::make('score'),
            ],
            'academic_program' => [
                Tables\Columns\TextColumn::make('data.program_name')->label('Name of Program')->wrap(),
                Tables\Columns\TextColumn::make('data.program_type')->label('Type of Program')->badge(),
                Tables\Columns\TextColumn::make('data.role')->label('Role'),
                Tables\Columns\TextColumn::make('data.academic_year_implemented')->label('AY Implemented'),
                ScoreColumn::make('score'),
            ],
            default => [],
        };
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
                ->modalHeading(fn() => 'Submit New ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl')
                ->after(fn() => $this->mount()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn() => 'Edit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl')
                ->visible($this->getActionVisibility()),
            DeleteAction::make()
                ->after(fn() => $this->mount())
                ->visible($this->getActionVisibility()),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = [];

        switch ($this->activeTable) {
            case 'sole_authorship':
            case 'co_authorship':
                $schema = [
                    Textarea::make('data.title')
                        ->label('Title of Instructional Material')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Select::make('data.material_type')
                        ->label('Type of Instructional Material')
                        ->options([
                            'textbook' => 'Textbook',
                            'textbook_chapter' => 'Textbook Chapter',
                            'manual_module' => 'Manual/Module/Workbook',
                            'multimedia_material' => 'Multimedia Teaching Material',
                            'testing_material' => 'Testing Material',
                        ])
                        ->searchable()
                        ->required(),
                    TextInput::make('data.reviewer')->label('Reviewer or Its Equivalent')->maxLength(150)->required(),
                    TextInput::make('data.publisher')->label('Publisher/Repository')->maxLength(150)->required(),
                    DatePicker::make('data.date_published')
                        ->label('Date Published')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->live()
                        ->required(),
                    DatePicker::make('data.date_approved')
                        ->label('Date Approved for Use')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->minDate(fn(Get $get) => $get('data.date_published'))
                        ->required(),
                ];

                if ($this->activeTable === 'co_authorship') {
                    $schema[] = TrimmedIntegerInput::make('data.contribution_percentage')
                        ->label('% Contribution')
                        ->minValue(1)
                        ->maxValue(100)
                        ->required();
                }
                break;

            case 'academic_program':
                $currentYear = (int) date('Y');
                $academicYears = [];

                for ($i = 0; $i < 4; $i++) {
                    $startYear = $currentYear - $i;
                    $endYear = $startYear + 1;
                    $academicYears["{$startYear}-{$endYear}"] = "{$startYear}-{$endYear}";
                }

                $schema = [
                    TextInput::make('data.program_name')
                        ->label('Name of Academic Degree Program (provide complete name)')
                        ->maxLength(150)
                        ->required()
                        ->columnSpanFull(),
                    Select::make('data.program_type')
                        ->label('Type of Program')
                        ->options([
                            'New Program' => 'New Program',
                            'Revised Program' => 'Revised Program',
                        ])
                        ->searchable()
                        ->required(),
                    TextInput::make('data.board_approval')
                        ->label('Board Approval (Board Resolution No.)')
                        ->maxLength(150)
                        ->required(),
                    Select::make('data.academic_year_implemented')
                        ->label('Academic Year Implemented')
                        ->options($academicYears)
                        ->rule('in:' . implode(',', array_keys($academicYears)))
                        ->required(),
                    Select::make('data.role')
                        ->label('Role')
                        ->options([
                            'Lead' => 'Lead',
                            'Contributor' => 'Contributor',
                        ])
                        ->searchable()
                        ->required(),
                ];
                break;
        }

        $schema[] = $this->getKRAFileUploadComponent();

        return $schema;
    }
}
