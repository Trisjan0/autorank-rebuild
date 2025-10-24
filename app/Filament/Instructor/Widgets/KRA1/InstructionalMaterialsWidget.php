<?php

namespace App\Filament\Instructor\Widgets\KRA1;

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

class InstructionalMaterialsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a1.instructional-materials-widget';

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
        $typeMap = [
            'sole_authorship' => 'im-sole-authorship',
            'co_authorship' => 'im-co-authorship',
            'academic_program' => 'im-academic-program',
        ];
        $type = $typeMap[$this->activeTable] ?? 'im-sole-authorship';

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $type);
    }

    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        switch ($this->activeTable) {
            case 'sole_authorship':
            case 'co_authorship':
                $columns = [
                    Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                    Tables\Columns\TextColumn::make('data.material_type')->label('Material Type')->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())->badge(),
                    Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
                    Tables\Columns\TextColumn::make('data.date_approved')->label('Date Approved')->date(),
                ];
                if ($this->activeTable === 'co_authorship') {
                    $columns[] = Tables\Columns\TextColumn::make('data.contribution_percentage')->label('% Contribution')->suffix('%');
                }
                return $columns;
            case 'academic_program':
                return [
                    Tables\Columns\TextColumn::make('data.program_name')->label('Name of Program')->wrap(),
                    Tables\Columns\TextColumn::make('data.program_type')->label('Type of Program')->badge(),
                    Tables\Columns\TextColumn::make('data.role')->label('Role'),
                    Tables\Columns\TextColumn::make('data.academic_year_implemented')->label('AY Implemented'),
                ];
            default:
                return [];
        }
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
                    $data['category'] = 'KRA I';

                    $typeMap = [
                        'sole_authorship' => 'im-sole-authorship',
                        'co_authorship' => 'im-co-authorship',
                        'academic_program' => 'im-academic-program',
                    ];
                    $data['type'] = $typeMap[$this->activeTable] ?? 'im-sole-authorship';

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
            case 'sole_authorship':
            case 'co_authorship':
                $schema = [
                    Textarea::make('data.title')->label('Title of Instructional Material')->required()->columnSpanFull(),
                    Select::make('data.material_type')
                        ->label('Type of Instructional Material')
                        ->options([
                            'textbook' => 'Textbook',
                            'textbook_chapter' => 'Textbook Chapter',
                            'manual_module' => 'Manual/Module/Workbook',
                            'multimedia_material' => 'Multimedia Teaching Material',
                            'testing_material' => 'Testing Material',
                        ])
                        ->required(),
                    TextInput::make('data.reviewer')->label('Reviewer or Its Equivalent')->required(),
                    TextInput::make('data.publisher')->label('Publisher/Repository')->required(),
                    DatePicker::make('data.date_published')->label('Date Published')->required(),
                    DatePicker::make('data.date_approved')->label('Date Approved for Use')->required(),
                ];
                if ($this->activeTable === 'co_authorship') {
                    $schema[] = TextInput::make('data.contribution_percentage')
                        ->label('% Contribution')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(100)
                        ->required();
                }
                break;
            case 'academic_program':
                $schema = [
                    TextInput::make('data.program_name')->label('Name of Academic Degree Program (provide complete name)')->required()->columnSpanFull(),
                    TextInput::make('data.program_type')->label('Type of Program')->required(),
                    TextInput::make('data.board_approval')->label('Board Approval (Board Resolution No.)')->required(),
                    TextInput::make('data.academic_year_implemented')->label('Academic Year Implemented')->required(),
                    TextInput::make('data.role')->label('Role')->required(),
                ];
                break;
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (Evidence Link)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra1-im/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
