<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Forms\Get;
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class InstructionalMaterialsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a1.instructional-materials-widget';

    public ?string $activeTable = 'sole_authorship';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading(fn(): ?string => $this->getTableHeading())
            ->columns($this->getTableColumns())
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions());
    }

    protected function getTableQuery(): Builder
    {
        $typeMap = [
            'sole_authorship' => 'im-sole-authorship',
            'co_authorship' => 'im-co-authorship',
            'academic_program' => 'im-academic-program',
        ];

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $typeMap[$this->activeTable] ?? 'im-sole-authorship');
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
                    $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                    $data['category'] = 'KRA I';

                    $typeMap = [
                        'sole_authorship' => 'im-sole-authorship',
                        'co_authorship' => 'im-co-authorship',
                        'academic_program' => 'im-academic-program',
                    ];

                    $data['type'] = $typeMap[$this->activeTable] ?? 'im-sole-authorship';
                    return $data;
                })
                ->modalHeading(fn() => 'Submit New ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn() => 'Edit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl'),
            DeleteAction::make(),
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

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s)')
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn() => 'proof-documents/kra1-im/' . $this->activeTable)
            ->maxFiles(5)
            ->maxSize(10240)
            ->acceptedFileTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'application/zip',
            ])
            ->helperText('Upload up to 5 proof documents (PDF, DOCX, XLSX, JPG, PNG, or ZIP, max 10MB each).')
            ->preserveFilenames()
            ->visibility('private')
            ->columnSpanFull();

        return $schema;
    }
}
