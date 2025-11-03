<?php

namespace App\Filament\Instructor\Widgets\KRA2;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class PublishedPapersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.published-papers-widget';

    public ?string $activeTable = 'sole_authorship';

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
        $type = $this->activeTable === 'sole_authorship' ? 'research-sole-authorship' : 'research-co-authorship';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
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
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
                ScoreColumn::make('score'),
            ],
            'co_authorship' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.output_type')
                    ->label('Output Type')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
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
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                    $data['category'] = 'KRA II';
                    $data['type'] = $this->activeTable === 'sole_authorship'
                        ? 'research-sole-authorship'
                        : 'research-co-authorship';

                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'sole_authorship'
                    ? 'Submit New Research Output (Sole Authorship)'
                    : 'Submit New Research Output (Co-Authorship)')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => $this->activeTable === 'sole_authorship'
                    ? 'Edit Research Output (Sole Authorship)'
                    : 'Edit Research Output (Co-Authorship)')
                ->modalWidth('3xl'),
            DeleteAction::make(),
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
                ->options([
                    'book' => 'Book',
                    'journal_article' => 'Journal Article',
                    'book_chapter' => 'Book Chapter',
                    'monograph' => 'Monograph',
                    'other_peer_reviewed_output' => 'Other Peer-Reviewed Output',
                ])
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

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => $this->activeTable === 'sole_authorship'
                ? 'proof-documents/kra2-research-sole'
                : 'proof-documents/kra2-research-co')
            ->columnSpanFull();

        return $schema;
    }
}
