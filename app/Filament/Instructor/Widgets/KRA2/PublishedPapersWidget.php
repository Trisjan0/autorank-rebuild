<?php

namespace App\Filament\Instructor\Widgets\KRA2;

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

class PublishedPapersWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.published-papers-widget';

    public ?string $activeTable = 'sole_authorship';

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
        $commonColumns = [
            Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
            Tables\Columns\TextColumn::make('data.research_type')
                ->label('Research Type')
                ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                ->badge(),
            Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
            Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
        ];

        if ($this->activeTable === 'co_authorship') {
            $commonColumns[] = Tables\Columns\TextColumn::make('data.contribution_percentage')
                ->label('% Contribution')
                ->suffix('%');
        }

        return $commonColumns;
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->required()
                ->columnSpanFull(),
            Select::make('data.research_type')
                ->label('Type of Research Output')
                ->options([
                    'research_paper' => 'Scholarly Research Paper',
                    'educational_article' => 'Educational Article',
                    'technical_article' => 'Technical Article',
                ])
                ->required(),
            TextInput::make('data.publisher')
                ->label('Name of Journal / Publisher')
                ->required(),
            TextInput::make('data.reviewer')
                ->label('Reviewer or Its Equivalent')
                ->required(),
            TextInput::make('data.indexing_body')
                ->label('International Indexing Body')
                ->required(),
            DatePicker::make('data.date_published')
                ->label('Date Published')
                ->required(),
        ];

        if ($this->activeTable === 'co_authorship') {
            $schema[] = TextInput::make('data.contribution_percentage')
                ->label('% Contribution')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->required();
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (Evidence Link)')
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
