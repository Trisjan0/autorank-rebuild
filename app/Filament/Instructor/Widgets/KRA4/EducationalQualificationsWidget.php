<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Application;
use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use App\Filament\Instructor\Widgets\BaseKRAWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Tables\Columns\ScoreColumn;
use App\Filament\Traits\HandlesKRAFileUploads;
use App\Tables\Actions\ViewSubmissionFilesAction;

class EducationalQualificationsWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a4.educational-qualifications-widget';

    public ?string $activeTable = 'doctorate_degree';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
    }

    public function getGoogleDriveFolderPath(): array
    {
        $kra = $this->getKACategory();
        $baseFolder = 'B: Educational Qualifications';

        switch ($this->activeTable) {
            case 'doctorate_degree':
                return [$kra, $baseFolder, 'Doctorate Degree'];
            case 'additional_degrees':
                return [$kra, $baseFolder, 'Additional Degrees'];
            default:
                return [$kra, $baseFolder, Str::slug($this->activeTable)];
        }
    }

    protected function isMultipleSubmissionAllowed(): bool
    {
        return $this->activeTable !== 'doctorate_degree';
    }

    protected function getKACategory(): string
    {
        return 'KRA IV';
    }

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'doctorate_degree'
            ? 'profdev-doctorate'
            : 'profdev-additional-degree';
    }

    protected function getOptionsMaps(): array
    {
        return [
            'degree_type' => [
                'additional_doctorate' => 'Additional Doctorate Degree',
                'additional_masters' => 'Additional Master\'s Degree',
                'post_doctorate_diploma' => 'Post-Doctorate Diploma/Certificate',
                'post_masters_diploma' => 'Post-Master\'s Diploma/Certificate',
            ],
        ];
    }

    public function getDisplayFormattingMap(): array
    {
        return [
            'Degree Type' => $this->getOptionsMaps()['degree_type'],
            'Date Completed' => 'm/d/Y',
        ];
    }

    public function table(Table $table): Table
    {
        $table = $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading(fn(): string => $this->getTableHeading())
            ->columns($this->getTableColumns())
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions())
            ->paginated(!$this->validation_mode)
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->emptyStateDescription($this->getTableEmptyStateDescription());

        if (!$this->validation_mode) {
            $table->checkIfRecordIsSelectableUsing(
                fn(Submission $record): bool => !$this->submissionExistsForCurrentType() || $record->id === $this->getCurrentSubmissionId()
            );
        }

        return $table;
    }

    protected function getTableQuery(): Builder
    {
        if ($this->validation_mode) {
            return Submission::query()
                ->where('application_id', $this->record->id)
                ->where('type', $this->getActiveSubmissionType());
        }

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType())
            ->where('application_id', $this->selectedApplicationId);
    }

    protected function getTableHeading(): string
    {
        return $this->activeTable === 'doctorate_degree'
            ? 'Doctorate Degree (First Time)'
            : 'Additional Degrees / Diplomas / Certificates';
    }

    protected function getTableColumns(): array
    {
        if ($this->activeTable === 'doctorate_degree') {
            return [
                Tables\Columns\TextColumn::make('data.name')->label('Name of Doctorate Degree')->wrap(),
                Tables\Columns\TextColumn::make('data.institution')->label('Name of Institution'),
                Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date('m/d/Y'),
                Tables\Columns\IconColumn::make('data.is_qualified')
                    ->label('Claimed for Sub-rank Increase?')
                    ->boolean(),
                ScoreColumn::make('score'),
            ];
        }

        return [
            Tables\Columns\TextColumn::make('data.degree_type')
                ->label('Type')
                ->formatStateUsing(fn(?string $state): string => $this->getOptionsMaps()['degree_type'][$state] ?? Str::of($state)->replace('_', ' ')->title())
                ->badge(),
            Tables\Columns\TextColumn::make('data.name')->label('Degree/Diploma/Cert Name')->wrap(),
            Tables\Columns\TextColumn::make('data.institution')->label('Name of HEI'),
            Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date('m/d/Y'),
            ScoreColumn::make('score'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema())
                ->disabled(function () {
                    $application = Application::find($this->selectedApplicationId);
                    if (!$application) {
                        return true;
                    }
                    return $application->status !== 'draft';
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = $this->selectedApplicationId;
                    $data['category'] = $this->getKACategory();
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Submit Doctorate Degree' : 'Submit Additional Qualification')
                ->modalWidth('3xl')
                ->hidden(fn(): bool => $this->submissionExistsForCurrentType() || $this->validation_mode)
                ->after(fn() => $this->mount()),
        ];
    }

    protected function getTableActions(): array
    {
        if ($this->validation_mode) {
            return [
                $this->getViewFilesAction(),
                $this->getValidateSubmissionAction(),
            ];
        }

        return [
            ViewSubmissionFilesAction::make(),
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Edit Doctorate Degree' : 'Edit Additional Qualification')
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

        if ($this->activeTable === 'doctorate_degree') {
            $schema = [
                TextInput::make('data.name')
                    ->label('Name of Doctorate Degree (complete name of the program)')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('data.institution')
                    ->label('Name of Institution Where the Degree Was Earned')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->required()
                    ->maxDate(now()),
                Toggle::make('data.is_qualified')
                    ->label('Is this degree being used for automatic 1 sub-rank increase?')
                    ->helperText('Check this ONLY if you are using this degree for automatic promotion instead of points in this evaluation.')
                    ->inline(false)
                    ->default(false),
            ];
        } else {
            $schema = [
                Select::make('data.degree_type')
                    ->label('Type')
                    ->options($this->getOptionsMaps()['degree_type'])
                    ->required()
                    ->searchable(),
                TextInput::make('data.name')
                    ->label('Name of Degree/Diploma/Certificate')
                    ->required()
                    ->maxLength(255),
                TextInput::make('data.institution')
                    ->label('Name of HEI')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->required()
                    ->maxDate(now()),
            ];
        }

        $schema[] = $this->getKRAFileUploadComponent();

        return $schema;
    }
}
