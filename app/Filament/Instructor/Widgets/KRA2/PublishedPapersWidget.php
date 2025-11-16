<?php

namespace App\Filament\Instructor\Widgets\KRA2;

use App\Models\Application;
use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;
use App\Filament\Traits\HandlesKRAFileUploads;
use App\Tables\Actions\ViewSubmissionFilesAction;

class PublishedPapersWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a2.published-papers-widget';

    public ?string $activeTable = 'sole_authorship';

    public function updatedActiveTable(): void
    {
        $this->resetTable();
    }

    public function getGoogleDriveFolderPath(): array
    {
        $kra = $this->getKACategory();

        switch ($this->activeTable) {
            case 'sole_authorship':
                return [$kra, 'A: Published Papers', 'Sole Authorship'];
            case 'co_authorship':
                return [$kra, 'A: Published Papers', 'Co-Authorship'];
            default:
                return [$kra, Str::slug($this->getActiveSubmissionType())];
        }
    }

    protected function getKACategory(): string
    {
        return 'KRA II';
    }

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'sole_authorship'
            ? 'research-sole-authorship'
            : 'research-co-authorship';
    }

    protected function getOptionsMaps(): array
    {
        return [
            'output_type' => [
                'book' => 'Book',
                'journal_article' => 'Journal Article',
                'book_chapter' => 'Book Chapter',
                'monograph' => 'Monograph',
                'other_peer_reviewed_output' => 'Other Peer-Reviewed Output',
            ],
        ];
    }

    public function getDisplayFormattingMap(): array
    {
        return [
            'Output Type' => $this->getOptionsMaps()['output_type'],
            'Date Published' => 'm/d/Y',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading(fn(): string => $this->getTableHeading())
            ->columns($this->getTableColumns())
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions())
            ->paginated(!$this->validation_mode)
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->emptyStateDescription($this->getTableEmptyStateDescription());
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
        return $this->activeTable === 'sole_authorship'
            ? 'Sole Authorship Submissions'
            : 'Co-Authorship Submissions';
    }

    protected function getTableColumns(): array
    {
        return match ($this->activeTable) {
            'sole_authorship' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.output_type')
                    ->label('Output Type')
                    ->formatStateUsing(fn(?string $state): string => $this->getOptionsMaps()['output_type'][$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date('m/d/Y'),
                ScoreColumn::make('score'),
            ],
            'co_authorship' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.output_type')
                    ->label('Output Type')
                    ->formatStateUsing(fn(?string $state): string => $this->getOptionsMaps()['output_type'][$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date('m/d/Y'),
                Tables\Columns\TextColumn::make('data.contribution_percentage')
                    ->label('% Contribution')
                    ->suffix('%'),
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
                ->disabled(function () {
                    $application = Application::find($this->selectedApplicationId);
                    if (!$application) {
                        return true;
                    }
                    return $application->status !== 'Draft';
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = $this->selectedApplicationId;
                    $data['category'] = $this->getKACategory();
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'sole_authorship'
                    ? 'Submit New Research Output (Sole Authorship)'
                    : 'Submit New Research Output (Co-Authorship)')
                ->modalWidth('3xl')
                ->hidden($this->validation_mode)
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
                ->modalHeading(fn(): string => $this->activeTable === 'sole_authorship'
                    ? 'Edit Research Output (Sole Authorship)'
                    : 'Edit Research Output (Co-Authorship)')
                ->modalWidth('3xl')
                ->visible($this->getActionVisibility()),
            DeleteAction::make()
                ->after(fn() => $this->mount())
                ->visible($this->getActionVisibility()),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = [
            Textarea::make('data.title')
                ->label('Title of Research Output')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),

            Select::make('data.output_type')
                ->label('Type of Research Output')
                ->options($this->getOptionsMaps()['output_type'])
                ->required()
                ->searchable()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state === 'journal_article') {
                        $set('data.reviewer', null);
                    } else {
                        $set('data.indexing_body', null);
                    }
                }),

            TextInput::make('data.publisher')
                ->label('Name of Journal / Publisher')
                ->maxLength(150)
                ->required(),

            TextInput::make('data.reviewer')
                ->label('Reviewer or Its Equivalent')
                ->maxLength(150)
                ->required()
                ->visible(fn(Get $get): bool => $get('data.output_type') !== 'journal_article'),

            TextInput::make('data.indexing_body')
                ->label('International Indexing Body')
                ->maxLength(150)
                ->required()
                ->visible(fn(Get $get): bool => $get('data.output_type') === 'journal_article'),

            DatePicker::make('data.date_published')
                ->label('Date Published')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->maxDate(now())
                ->required(),
        ];

        if ($this->activeTable === 'co_authorship') {
            $schema[] = TrimmedIntegerInput::make('data.contribution_percentage')
                ->label('% Contribution')
                ->minValue(1)
                ->maxValue(100)
                ->required();
        }

        $schema[] = $this->getKRAFileUploadComponent();

        return $schema;
    }
}
