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

class NonPatentableInventionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a2.non-patentable-inventions-widget';

    public ?string $activeTable = 'software_new_sole';

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

        if (Str::startsWith($this->activeTable, 'software')) {
            $columns[] = Tables\Columns\TextColumn::make('data.date_copyrighted')->label('Date Copyrighted')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.date_utilized')->label('Date Utilized')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.end_user')->label('End User(s)');
        } elseif (Str::startsWith($this->activeTable, 'plant_animal')) {
            $columns[] = Tables\Columns\TextColumn::make('data.type')->label('Type')->badge();
            $columns[] = Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date();
            $columns[] = Tables\Columns\TextColumn::make('data.date_registered')->label('Date Registered')->date();
        }

        if ($this->activeTable === 'software_new_multiple' || $this->activeTable === 'plant_animal_multiple') {
            $columns[] = Tables\Columns\TextColumn::make('data.contribution_percentage')
                ->label('% Contribution')
                ->suffix('%');
        } elseif ($this->activeTable === 'software_updated') {
            $columns[] = Tables\Columns\TextColumn::make('data.contribution')->label('Contribution');
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

        if (Str::startsWith($this->activeTable, 'software_new')) {
            $schema = [
                TextInput::make('data.name')
                    ->label('Name of the Software')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('data.date_copyrighted')
                    ->label('Date Copyrighted')
                    ->required(),
                DatePicker::make('data.date_utilized')
                    ->label('Date Utilized')
                    ->required(),
                TextInput::make('data.end_user')
                    ->label('Name of End User/s')
                    ->required(),
            ];
        } elseif ($this->activeTable === 'software_updated') {
            $schema = [
                TextInput::make('data.name')
                    ->label('Name of the Software')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('data.date_copyrighted')
                    ->label('Date Copyrighted')
                    ->required(),
                DatePicker::make('data.date_utilized')
                    ->label('Date Utilized')
                    ->required(),
                TextInput::make('data.contribution')
                    ->label('Contribution (e.g., Sole, 50%)')
                    ->required(),
                Textarea::make('data.new_features')
                    ->label('Specify New Features')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('data.end_user')
                    ->label('Name of End User/s')
                    ->required(),
            ];
        } elseif (Str::startsWith($this->activeTable, 'plant_animal')) {
            $schema = [
                TextInput::make('data.name')
                    ->label('Name of Plant Variety, Animal Breed, or Microbial Strain')
                    ->required()
                    ->columnSpanFull(),
                Select::make('data.type')
                    ->label('Type')
                    ->options([
                        'plant' => 'Plant',
                        'animal' => 'Animal',
                        'microbe' => 'Microbe',
                    ])
                    ->required(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->required(),
                DatePicker::make('data.date_registered')
                    ->label('Date Registered')
                    ->required(),
                DatePicker::make('data.date_propagation')
                    ->label('Date of Propagation based on Certification')
                    ->required(),
            ];
        }

        if ($this->activeTable === 'software_new_multiple' || $this->activeTable === 'plant_animal_multiple') {
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
            ->directory(fn(): string => 'proof-documents/kra2-nonpatent/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
