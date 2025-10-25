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

class TranslatedOutputsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.translated-outputs-widget';

    public ?string $activeTable = 'lead_researcher';

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
        return $this->activeTable === 'lead_researcher'
            ? 'research-translated-lead'
            : 'research-translated-contributor';
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
        return match ($this->activeTable) {
            'lead_researcher' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title of Research')->wrap(),
                Tables\Columns\TextColumn::make('data.project_name')->label('Project/Policy/Product Name')->wrap(),
                Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
                Tables\Columns\TextColumn::make('data.date_utilized')->label('Date Utilized/Implemented')->date(),
                Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
            ],
            'contributor' => [
                Tables\Columns\TextColumn::make('data.title')->label('Title of Research')->wrap(),
                Tables\Columns\TextColumn::make('data.project_name')->label('Project/Policy/Product Name')->wrap(),
                Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
                Tables\Columns\TextColumn::make('data.date_utilized')->label('Date Utilized/Implemented')->date(),
                Tables\Columns\TextColumn::make('data.contribution_percentage')
                    ->label('% Contribution')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
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
                ->modalHeading(fn(): string => 'Submit New Translated Output (' . Str::of($this->activeTable)->replace('_', ' ')->title() . ')')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => 'Edit Translated Output (' . Str::of($this->activeTable)->replace('_', ' ')->title() . ')')
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = [
            Textarea::make('data.title')
                ->label('Title of Research')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_completed')
                ->label('Date Completed')
                ->maxDate(now())
                ->required(),
            TextInput::make('data.funding_source')
                ->label('Funding Source')
                ->maxLength(150)
                ->required(),
            Textarea::make('data.project_name')
                ->label('Name of Project, Policy or Product')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_utilized')
                ->label('Date Utilized / Implemented / Adopted / Developed')
                ->maxDate(now())
                ->required(),
        ];

        if ($this->activeTable === 'contributor') {
            $schema[] = TextInput::make('data.contribution_percentage')
                ->label('% Contribution')
                ->integer()
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
            ->directory(fn(): string => 'proof-documents/kra2-translated/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
