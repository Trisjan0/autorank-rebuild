<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
use App\Tables\Columns\ScoreColumn;

class EducationalQualificationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a4.educational-qualifications-widget';

    public ?string $activeTable = 'doctorate_degree';

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
        $type = $this->getActiveSubmissionType();

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('category', 'KRA IV')
            ->where('type', $type)
            ->where('application_id', Auth::user()?->activeApplication?->id ?? null);
    }

    protected function getActiveSubmissionType(): string
    {
        return $this->activeTable === 'doctorate_degree'
            ? 'profdev-doctorate'
            : 'profdev-additional-degree';
    }

    protected function getTableHeading(): string
    {
        return $this->activeTable === 'doctorate_degree'
            ? 'Doctorate Degree (First Time)'
            : 'Additional Degrees / Diplomas / Certificates';
    }

    protected function getTableColumns(): array
    {
        if ($this->activeTable === 'doctorate_degree') {
            return [
                Tables\Columns\TextColumn::make('data.name')->label('Name of Doctorate Degree')->wrap(),
                Tables\Columns\TextColumn::make('data.institution')->label('Name of Institution'),
                Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
                Tables\Columns\IconColumn::make('data.is_qualified')
                    ->label('Claimed for Sub-rank Increase?')
                    ->boolean(),
                ScoreColumn::make('score'),
            ];
        }

        return [
            Tables\Columns\TextColumn::make('data.degree_type')
                ->label('Type')
                ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                ->badge(),
            Tables\Columns\TextColumn::make('data.name')->label('Degree/Diploma/Cert Name')->wrap(),
            Tables\Columns\TextColumn::make('data.institution')->label('Name of HEI'),
            Tables\Columns\TextColumn::make('data.date_completed')->label('Date Completed')->date(),
            ScoreColumn::make('score'),
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
                    $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                    $data['category'] = 'KRA IV';
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Submit Doctorate Degree' : 'Submit Additional Qualification')
                ->modalWidth('3xl'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading(fn(): string => $this->activeTable === 'doctorate_degree' ? 'Edit Doctorate Degree' : 'Edit Additional Qualification')
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
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('data.institution')
                    ->label('Name of Institution Where the Degree Was Earned')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->required()
                    ->maxDate(now()),
                Toggle::make('data.is_qualified')
                    ->label('Is this degree being used for automatic 1 sub-rank increase?')
                    ->helperText('Check this ONLY if you are using this degree for automatic promotion instead of points in this evaluation.')
                    ->inline(false)
                    ->default(false),
            ];
        } else {
            $schema = [
                Select::make('data.degree_type')
                    ->label('Type')
                    ->options([
                        'additional_doctorate' => 'Additional Doctorate Degree',
                        'additional_masters' => 'Additional Master\'s Degree',
                        'post_doctorate_diploma' => 'Post-Doctorate Diploma/Certificate',
                        'post_masters_diploma' => 'Post-Master\'s Diploma/Certificate',
                    ])
                    ->required()
                    ->searchable(),
                TextInput::make('data.name')
                    ->label('Name of Degree/Diploma/Certificate')
                    ->required()
                    ->maxLength(255),
                TextInput::make('data.institution')
                    ->label('Name of HEI')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                DatePicker::make('data.date_completed')
                    ->label('Date Completed')
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->required()
                    ->maxDate(now()),
            ];
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (e.g., TOR, Diploma, Certificate)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra4-degrees/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
