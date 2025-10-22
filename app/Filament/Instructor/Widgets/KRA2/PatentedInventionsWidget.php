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

class PatentedInventionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.patented-inventions-widget';

    public ?string $activeTable = 'invention_sole';

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
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->activeTable);
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('data.name')->label('Name')->wrap(),
        ];

        if (Str::startsWith($this->activeTable, 'invention')) {
            $columns[] = Tables\Columns\TextColumn::make('data.application_date')->label('Application Date')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.patent_stage')->label('Patent Stage');
        } elseif (Str::startsWith($this->activeTable, 'utility_design')) {
            $columns[] = Tables\Columns\TextColumn::make('data.patent_type')->label('Type of Patent')->badge();
            $columns[] = Tables\Columns\TextColumn::make('data.application_date')->label('Application Date')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.date_granted')->label('Date Granted')->date();
        } elseif (Str::startsWith($this->activeTable, 'commercialized')) {
            $columns[] = Tables\Columns\TextColumn::make('data.patent_type')->label('Type of Product')->badge();
            $columns[] = Tables\Columns\TextColumn::make('data.date_patented')->label('Date Patented')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.date_commercialized')->label('Date Commercialized')->date();
        }

        if (Str::contains($this->activeTable, 'multiple')) {
            $columns[] = Tables\Columns\TextColumn::make('data.contribution_percentage')
                ->label('% Contribution')
                ->suffix('%');
        }

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
                    $data['application_id'] = Auth::user()->activeApplication->id;
                    $data['category'] = 'KRA II';
                    $data['type'] = $this->activeTable;
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

        if (Str::startsWith($this->activeTable, 'invention')) {
            $schema = [
                Textarea::make('data.name')
                    ->label('Name of the Invention')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('data.application_date')
                    ->label('Application Date')
                    ->required(),
                TextInput::make('data.patent_stage')
                    ->label('Patent Application Stage')
                    ->required(),
                DatePicker::make('data.date_granted')
                    ->label('Date Accepted, Published or Granted')
                    ->required(),
            ];
        } elseif (Str::startsWith($this->activeTable, 'utility_design')) {
            $schema = [
                Textarea::make('data.name')
                    ->label('Name of Invention')
                    ->required()
                    ->columnSpanFull(),
                Select::make('data.patent_type')
                    ->label('Type of Patent')
                    ->options([
                        'utility_model' => 'Utility Model',
                        'industrial_design' => 'Industrial Design',
                    ])
                    ->required(),
                DatePicker::make('data.application_date')
                    ->label('Date of Application')
                    ->required(),
                DatePicker::make('data.date_granted')
                    ->label('Date Granted')
                    ->required(),
            ];
        } elseif (Str::startsWith($this->activeTable, 'commercialized')) {
            $schema = [
                Textarea::make('data.name')
                    ->label('Name of Patented Product')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('data.patent_type')
                    ->label('Type of Product')
                    ->required(),
                DatePicker::make('data.date_patented')
                    ->label('Date Patented')
                    ->required(),
                DatePicker::make('data.date_commercialized')
                    ->label('Date Product was First Commercialized')
                    ->required(),
                TextInput::make('data.area_commercialized')
                    ->label('Area/Place Commercialized')
                    ->required(),
            ];
        }

        if (Str::contains($this->activeTable, 'multiple')) {
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
            ->directory(fn(): string => 'proof-documents/kra2-patents/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
