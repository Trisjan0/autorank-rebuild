<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
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
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class MentorshipServicesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a1.mentorship-services-widget';

    public ?string $activeTable = 'adviser';

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
            ->actions($this->getTableActions());
    }

    protected function getTableQuery(): Builder
    {
        $typeMap = [
            'adviser' => 'mentorship-adviser',
            'panel' => 'mentorship-panel',
            'mentor' => 'mentorship-mentor',
        ];
        $type = $typeMap[$this->activeTable] ?? 'mentorship-adviser';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Submissions';
    }

    /**
     * Helper function to get dynamic Academic Year labels for the table.
     */
    private function getTableAcademicYearColumns(): array
    {
        $currentYear = (int) date('Y');
        $columns = [];

        for ($i = 0; $i < 4; $i++) {
            $startYear = $currentYear - ($i + 1);
            $endYear = $startYear + 1;
            $label = "AY {$startYear}–{$endYear}";
            $key = 'data.ay_' . (4 - $i) . '_count';

            $columns[] = Tables\Columns\TextColumn::make($key)->label($label);
        }

        return array_reverse($columns);
    }

    protected function getTableColumns(): array
    {
        return match ($this->activeTable) {
            'adviser', 'panel' => [
                Tables\Columns\TextColumn::make('data.mentorship_type')
                    ->label('Type')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),

                ...$this->getTableAcademicYearColumns(),

                ScoreColumn::make('score'),
            ],
            'mentor' => [
                Tables\Columns\TextColumn::make('data.competition_name')->label('Name of Competition')->wrap(),
                Tables\Columns\TextColumn::make('data.award_received')->label('Award Received'),
                Tables\Columns\TextColumn::make('data.date_awarded')->label('Date Awarded')->date(),
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
                        'adviser' => 'mentorship-adviser',
                        'panel' => 'mentorship-panel',
                        'mentor' => 'mentorship-mentor',
                    ];
                    $data['type'] = $typeMap[$this->activeTable] ?? 'mentorship-adviser';

                    return $data;
                })
                ->modalHeading(fn(): string => 'Submit New ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => 'Edit ' . Str::of($this->activeTable)->replace('_', ' ')->title())
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    /**
     * Helper function to get dynamic Academic Year fields for the form.
     */
    private function getAcademicYearFields(): array
    {
        $labelPrefix = $this->activeTable === 'adviser' ? 'No. of Student Advisees' : 'No. of Times as Panel';
        $currentYear = (int) date('Y');
        $fields = [];

        for ($i = 0; $i < 4; $i++) {
            $startYear = $currentYear - ($i + 1);
            $endYear = $startYear + 1;
            $label = "{$labelPrefix} (AY {$startYear}–{$endYear})";
            $key = 'ay_' . (4 - $i) . '_count';

            $fields[] = TrimmedIntegerInput::make('data.' . $key)
                ->label($label)
                ->required()
                ->default(0)
                ->minValue(0);
        }

        return array_reverse($fields);
    }

    protected function getFormSchema(): array
    {
        $schema = match ($this->activeTable) {
            'adviser', 'panel' => [
                Select::make('data.mentorship_type')
                    ->label('Mentorship Type')
                    ->options([
                        'special_capstone_project' => 'Special/Capstone Project',
                        'undergrad_thesis' => 'Undergrad Thesis',
                        'masters_thesis' => 'Masters Thesis',
                        'dissertation' => 'Dissertation',
                    ])
                    ->searchable()
                    ->required(),

                ...$this->getAcademicYearFields(),
            ],
            'mentor' => [
                TextInput::make('data.competition_name')->label('Name of the Academic Competition')->maxLength(150)->required()->columnSpanFull(),
                TextInput::make('data.sponsor')->label('Name of Sponsor Organization')->maxLength(150)->required(),
                TextInput::make('data.award_received')->label('Award Received')->maxLength(150)->required(),
                DatePicker::make('data.date_awarded')
                    ->label('Date Awarded')
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->maxDate(now())
                    ->required(),
            ],
            default => [],
        };

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra1-mentor/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
