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

class TranslatedOutputsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.translated-outputs-widget';

    public ?string $activeTable = 'lead_researcher';

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
        $type = $this->activeTable === 'lead_researcher' ? 'research-lead-researcher' : 'research-contributor';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
    }

    protected function getTableHeading(): string
    {
        return $this->activeTable === 'lead_researcher'
            ? 'Lead Researcher Submissions'
            : 'Contributor Submissions';
    }

    protected function getTableColumns(): array
    {
        $commonColumns = [
            Tables\Columns\TextColumn::make('data.title')->label('Title of Research')->wrap(),
            Tables\Columns\TextColumn::make('data.project_name')->label('Project/Policy/Product Name')->wrap(),
            Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
            Tables\Columns\TextColumn::make('data.date_implemented')->label('Date Implemented')->date(),
        ];

        if ($this->activeTable === 'contributor') {
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

                    $data['type'] = $this->activeTable === 'lead_researcher'
                        ? 'research-lead-researcher'
                        : 'research-contributor';

                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'lead_researcher'
                    ? 'Submit New Translated Output (Lead Researcher)'
                    : 'Submit New Translated Output (Contributor)')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => $this->activeTable === 'lead_researcher'
                    ? 'Edit Translated Output (Lead Researcher)'
                    : 'Edit Translated Output (Contributor)')
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = [
            Textarea::make('data.title')
                ->label('Title of Research')
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_completed')
                ->label('Date Completed')
                ->required(),
            TextInput::make('data.funding_source')
                ->label('Funding Source')
                ->required(),
            Textarea::make('data.project_name')
                ->label('Name of Project, Policy or Product')
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_implemented')
                ->label('Date Implemented, Adopted or Developed')
                ->required(),
        ];

        if ($this->activeTable === 'contributor') {
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
            ->directory(fn(): string => $this->activeTable === 'lead_researcher'
                ? 'proof-documents/kra2-lead-researcher'
                : 'proof-documents/kra2-contributor')
            ->columnSpanFull();

        return $schema;
    }
}
