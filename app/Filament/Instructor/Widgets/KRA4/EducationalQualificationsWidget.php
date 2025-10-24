<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EducationalQualificationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a4.educational-qualifications-widget';

    public ?string $activeTable = 'doctorate_degree';

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
        $type = $this->activeTable === 'doctorate_degree' ? 'profdev-doctorate' : 'profdev-additional-degree';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
    }

    protected function getTableHeading(): string
    {
        return $this->activeTable === 'doctorate_degree'
            ? 'Doctorate Degree (First Time)'
            : 'Additional Degrees';
    }

    protected function getTableColumns(): array
    {
        if ($this->activeTable === 'doctorate_degree') {
            return [
                Tables\Columns\TextColumn::make('data.name')->label('Name of Doctorate Degree')->wrap(),
                Tables\Columns\TextColumn::make('data.institution')->label('Name of Institution'),
                Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
                Tables\Columns\IconColumn::make('data.is_qualified')->label('Qualified for Sub-rank Increase?')->boolean(),
            ];
        }

        return [
            Tables\Columns\TextColumn::make('data.degree_type')->label('Degree')->badge(),
            Tables\Columns\TextColumn::make('data.name')->label('Degree Name')->wrap(),
            Tables\Columns\TextColumn::make('data.institution')->label('Name of HEI'),
            Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
        ];
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
                    $data['category'] = 'KRA IV';
                    $data['type'] = $this->activeTable === 'doctorate_degree' ? 'profdev-doctorate' : 'profdev-additional-degree';
                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Submit Doctorate Degree' : 'Submit Additional Degree')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Edit Doctorate Degree' : 'Edit Additional Degree')
                ->modalWidth('3xl'),
            DeleteAction::make(),
        ];
    }

    protected function getFormSchema(): array
    {
        $schema = [];

        if ($this->activeTable === 'doctorate_degree') {
            $schema = [
                TextInput::make('data.name')
                    ->label('Name of Doctorate Degree (complete name of the program)')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('data.institution')
                    ->label('Name of Institution Where the Degree Was Earned')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->required(),
                Toggle::make('data.is_qualified')
                    ->label('Is the Faculty Qualified for the Automatic 1 Sub-rank Increase?')
                    ->inline(false),
            ];
        } else {
            $schema = [
                Select::make('data.degree_type')
                    ->label('Degree')
                    ->options([
                        'masters' => 'Master\'s Degree',
                        'bachelors' => 'Bachelor\'s Degree',
                    ])
                    ->required(),
                TextInput::make('data.name')
                    ->label('Degree Name')
                    ->required(),
                TextInput::make('data.institution')
                    ->label('Name of HEI')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->required(),
            ];
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (Evidence Link)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra4-degrees/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
