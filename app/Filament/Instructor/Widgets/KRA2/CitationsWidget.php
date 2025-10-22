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

class CitationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.citations-widget';

    public ?string $activeTable = 'local_authors';

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
        $type = $this->activeTable === 'local_authors' ? 'research-citation-local' : 'research-citation-international';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
    }

    // 5. Helper method to set the correct table heading
    protected function getTableHeading(): string
    {
        return $this->activeTable === 'local_authors'
            ? 'Local Author Citation Submissions'
            : 'International Author Citation Submissions';
    }

    // 6. Helper method to get the columns (identical for both)
    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('data.title')->label('Title of Journal Article')->wrap(),
            Tables\Columns\TextColumn::make('data.journal_name')->label('Name of Journal'),
            Tables\Columns\TextColumn::make('data.citation_count')->label('No. of Citation'),
            Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
        ];
    }

    // 7. Helper method to get the correct header actions
    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema()) // Form is static
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = Auth::user()->activeApplication->id;
                    $data['category'] = 'KRA II';

                    // Dynamically set the 'type' based on the active tab
                    $data['type'] = $this->activeTable === 'local_authors'
                        ? 'research-citation-local'
                        : 'research-citation-international';

                    return $data;
                })
                // Dynamically set the modal heading
                ->modalHeading(fn(): string => $this->activeTable === 'local_authors'
                    ? 'Submit New Citation (Local Authors)'
                    : 'Submit New Citation (International Authors)')
                ->modalWidth('3xl'),
        ];
    }

    // 8. Helper method to get the correct row actions
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema()) // Re-uses the static form
                ->modalHeading(fn(): string => $this->activeTable === 'local_authors'
                    ? 'Edit Citation (Local Authors)'
                    : 'Edit Citation (International Authors)')
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    // 9. Helper method to get the form schema (identical for both)
    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Journal Article')
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.date_published')
                ->label('Date Published')
                ->required(),
            TextInput::make('data.journal_name')
                ->label('Name of Journal')
                ->required(),
            TextInput::make('data.citation_count')
                ->label('No. of Citation')
                ->numeric()
                ->required(),
            TextInput::make('data.citation_index')
                ->label('Citation Index')
                ->required(),
            TextInput::make('data.citation_year')
                ->label('Year/s Citation Published')
                ->helperText('Can be a single year (e.g., 2023) or a range (e.g., 2022-2023).')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                // Dynamically set the upload directory
                ->directory(fn(): string => $this->activeTable === 'local_authors'
                    ? 'proof-documents/kra2-citation-local'
                    : 'proof-documents/kra2-citation-intl')
                ->columnSpanFull(),
        ];
    }
}
