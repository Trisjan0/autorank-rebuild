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
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class PatentedInventionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.patented-inventions-widget';

    public ?string $activeTable = 'invention_patent_sole';

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
        return match ($this->activeTable) {
            'invention_patent_sole' => 'invention-patent-sole',
            'invention_patent_co' => 'invention-patent-co-inventor',
            'utility_design_sole' => 'invention-utility-design-sole',
            'utility_design_co' => 'invention-utility-design-co-inventor',
            'commercialized_local' => 'invention-commercialized-local',
            'commercialized_intl' => 'invention-commercialized-international',
            default => 'invention-patent-sole',
        };
    }

    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType());
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)
            ->replace('_', ' ')
            ->replace(' co ', ' Co-Inventor ')
            ->replace(' intl', ' International')
            ->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        $columns = [];

        $columns[] = Tables\Columns\TextColumn::make('data.name')->label('Name')->wrap();

        switch ($this->activeTable) {
            case 'invention_patent_sole':
            case 'invention_patent_co':
                $columns[] = Tables\Columns\TextColumn::make('data.application_date')->label('Application Date')->date();
                $columns[] = Tables\Columns\TextColumn::make('data.patent_stage')->label('Patent Stage')->formatStateUsing(fn(?string $state): string => Str::title($state ?? ''))->badge();
                break;
            case 'utility_design_sole':
            case 'utility_design_co':
                $columns[] = Tables\Columns\TextColumn::make('data.patent_type')->label('Type')->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())->badge();
                $columns[] = Tables\Columns\TextColumn::make('data.application_date')->label('Application Date')->date();
                $columns[] = Tables\Columns\TextColumn::make('data.date_granted')->label('Date Granted')->date();
                break;
            case 'commercialized_local':
            case 'commercialized_intl':
                $columns[] = Tables\Columns\TextColumn::make('data.patent_type')->label('Type of Product');
                $columns[] = Tables\Columns\TextColumn::make('data.date_patented')->label('Date Patented')->date();
                $columns[] = Tables\Columns\TextColumn::make('data.date_commercialized')->label('Date Commercialized')->date();
                $columns[] = Tables\Columns\TextColumn::make('data.area_commercialized')->label('Area Commercialized');
                break;
        }

        if (in_array($this->activeTable, ['invention_patent_co', 'utility_design_co'])) {
            $columns[] = Tables\Columns\TextColumn::make('data.contribution_percentage')->label('% Contribution')->suffix('%');
        }

        $columns[] = ScoreColumn::make('score');

        return $columns;
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

    protected function getFormSchema(): array
    {
        $schema = [];

        switch ($this->activeTable) {
            case 'invention_patent_sole':
            case 'invention_patent_co':
                $schema = [
                    Textarea::make('data.name')->label('Name of the Invention')->maxLength(255)->required()->columnSpanFull(),
                    DatePicker::make('data.application_date')
                        ->label('Application Date')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                    Select::make('data.patent_stage')
                        ->label('Patent Application Stage')
                        ->options(['accepted' => 'Accepted', 'published' => 'Published', 'granted' => 'Granted'])
                        ->searchable()
                        ->required(),
                    DatePicker::make('data.date_granted')
                        ->label('Date Accepted / Published / Granted')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                ];
                break;
            case 'utility_design_sole':
            case 'utility_design_co':
                $schema = [
                    Textarea::make('data.name')->label('Name of Invention/Design')->maxLength(255)->required()->columnSpanFull(),
                    Select::make('data.patent_type')
                        ->label('Type of Patent')
                        ->options(['utility_model' => 'Utility Model', 'industrial_design' => 'Industrial Design'])
                        ->searchable()
                        ->required(),
                    DatePicker::make('data.application_date')
                        ->label('Date of Application')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                    DatePicker::make('data.date_granted')
                        ->label('Date Granted')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                ];
                break;
            case 'commercialized_local':
            case 'commercialized_intl':
                $schema = [
                    Textarea::make('data.name')->label('Name of Patented Product')->maxLength(255)->required()->columnSpanFull(),
                    TextInput::make('data.patent_type')->label('Type of Product')->maxLength(100)->required(),
                    DatePicker::make('data.date_patented')
                        ->label('Date Patented')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                    DatePicker::make('data.date_commercialized')
                        ->label('Date Product was First Commercialized')
                        ->native(false)
                        ->displayFormat('m/d/Y')
                        ->maxDate(now())
                        ->required(),
                    TextInput::make('data.area_commercialized')->label('Area/Place Commercialized')->maxLength(150)->required(),
                ];
                break;
        }

        if (in_array($this->activeTable, ['invention_patent_co', 'utility_design_co'])) {
            $schema[] = TrimmedIntegerInput::make('data.contribution_percentage')
                ->label('% Contribution')
                ->minValue(1)
                ->maxValue(100)
                ->required();
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s)')
            ->multiple()->reorderable()->required()->disk('private')
            ->directory(fn(): string => 'proof-documents/kra2-patents/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
