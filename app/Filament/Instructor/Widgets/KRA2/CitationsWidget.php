<?php

namespace App\Filament\Instructor\Widgets\KRA2;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class CitationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a2.citations-widget';

    public ?string $activeTable = 'local_authors';

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

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'local_authors'
            ? 'research-citation-local'
            : 'research-citation-international';
    }

    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType());
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Citation Submissions';
    }

    protected function getTableColumns(): array
    {
        return match ($this->activeTable) {
            'local_authors', 'international_authors' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title of Journal Article')->wrap(),
                Tables\Columns\TextColumn::make('data.journal_name')->label('Name of Journal'),
                Tables\Columns\TextColumn::make('data.citation_count')->label('No. of Citations')->numeric(),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
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
                    $data['category'] = 'KRA II';
                    $data['type'] = $this->getActiveSubmissionType();

                    return $data;
                })
                ->modalHeading(fn(): string => 'Submit New Citation (' . Str::of($this->activeTable)->replace('_', ' ')->title() . ')')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => 'Edit Citation (' . Str::of($this->activeTable)->replace('_', ' ')->title() . ')')
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Journal Article')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_published')
                ->label('Date Published')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->maxDate(now())
                ->required(),
            TextInput::make('data.journal_name')
                ->label('Name of Journal')
                ->maxLength(150)
                ->required(),
            TrimmedIntegerInput::make('data.citation_count')
                ->label('No. of Citations')
                ->minValue(0)
                ->required(),
            TextInput::make('data.citation_index')
                ->label('Citation Index (e.g., Scopus, Google Scholar)')
                ->maxLength(100)
                ->required(),
            TextInput::make('data.citation_year')
                ->label('Year/s Citation Published')
                ->helperText('Can be a single year (e.g., 2023) or a range (e.g., 2022-2023).')
                ->maxLength(50)
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory(fn(): string => 'proof-documents/kra2-citation/' . $this->activeTable)
                ->columnSpanFull(),
        ];
    }
}
