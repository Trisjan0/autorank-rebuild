<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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

class MentorshipServicesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a1.mentorship-services-widget';

    public ?string $activeTable = 'adviser';

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
            'adviser' => 'mentorship-adviser',
            'panel' => 'mentorship-panel',
            'mentor' => 'mentorship-mentor',
        ];
        $type = $typeMap[$this->activeTable] ?? 'mentorship-adviser';

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
            case 'adviser':
                return [
                    Tables\Columns\TextColumn::make('data.requirement')->label('Requirement')->badge(),
                    Tables\Columns\TextColumn::make('data.student_count_ay_1')->label('AY 2019–2020'),
                    Tables\Columns\TextColumn::make('data.student_count_ay_2')->label('AY 2020–2021'),
                    Tables\Columns\TextColumn::make('data.student_count_ay_3')->label('AY 2021–2022'),
                    Tables\Columns\TextColumn::make('data.student_count_ay_4')->label('AY 2022–2023'),
                ];
            case 'panel':
                return [
                    Tables\Columns\TextColumn::make('data.requirement')->label('Requirement')->badge(),
                    Tables\Columns\TextColumn::make('data.panel_count_ay_1')->label('AY 2019–2020'),
                    Tables\Columns\TextColumn::make('data.panel_count_ay_2')->label('AY 2020–2021'),
                    Tables\Columns\TextColumn::make('data.panel_count_ay_3')->label('AY 2021–2022'),
                    Tables\Columns\TextColumn::make('data.panel_count_ay_4')->label('AY 2022–2023'),
                ];
            case 'mentor':
                return [
                    Tables\Columns\TextColumn::make('data.competition_name')->label('Name of Competition')->wrap(),
                    Tables\Columns\TextColumn::make('data.award_received')->label('Award Received'),
                    Tables\Columns\TextColumn::make('data.date_awarded')->label('Date Awarded')->date(),
                ];
            default:
                return [];
        }
    }

    protected function adviserPanelSubmissionExists(): bool
    {
        if ($this->activeTable !== 'adviser' && $this->activeTable !== 'panel') {
            return false;
        }

        $activeApplicationId = Auth::user()->activeApplication->id ?? null;
        if (!$activeApplicationId) {
            return true;
        }

        $type = $this->activeTable === 'adviser' ? 'mentorship-adviser' : 'mentorship-panel';

        return Submission::where('user_id', Auth::id())
            ->where('application_id', $activeApplicationId)
            ->where('type', $type)
            ->exists();
    }

    protected function getTableHeaderActions(): array
    {
        $createAction = CreateAction::make()
            ->label('Add')
            ->form($this->getFormSchema())
            ->mutateFormDataUsing(function (array $data): array {
                $data['user_id'] = Auth::id();
                $data['application_id'] = Auth::user()->activeApplication->id;
                $data['category'] = 'KRA I';

                $typeMap = [
                    'adviser' => 'mentorship-adviser',
                    'panel' => 'mentorship-panel',
                    'mentor' => 'mentorship-mentor',
                ];
                $data['type'] = $typeMap[$this->activeTable] ?? 'mentorship-adviser';

                return $data;
            })
            ->modalHeading(fn(): string => 'Submit New ' . Str::of($this->activeTable)->replace('_', ' ')->title())
            ->modalWidth('3xl');

        if ($this->activeTable === 'adviser' || $this->activeTable === 'panel') {
            $createAction->hidden(fn(): bool => $this->adviserPanelSubmissionExists());
        }

        return [$createAction];
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
            case 'adviser':
                $schema = [
                    TextInput::make('data.requirement')->label('Requirement')->required(), // Or Select if predefined list
                    TextInput::make('data.student_count_ay_1')->label('No. of Student Advisees (AY 2019–2020)')->numeric()->required()->default(0),
                    TextInput::make('data.student_count_ay_2')->label('No. of Student Advisees (AY 2020–2021)')->numeric()->required()->default(0),
                    TextInput::make('data.student_count_ay_3')->label('No. of Student Advisees (AY 2021–2022)')->numeric()->required()->default(0),
                    TextInput::make('data.student_count_ay_4')->label('No. of Student Advisees (AY 2022–2023)')->numeric()->required()->default(0),
                ];
                break;
            case 'panel':
                $schema = [
                    TextInput::make('data.requirement')->label('Requirement')->required(), // Or Select if predefined list
                    TextInput::make('data.panel_count_ay_1')->label('No. of Times as Panel Member (AY 2019–2020)')->numeric()->required()->default(0),
                    TextInput::make('data.panel_count_ay_2')->label('No. of Times as Panel Member (AY 2020–2021)')->numeric()->required()->default(0),
                    TextInput::make('data.panel_count_ay_3')->label('No. of Times as Panel Member (AY 2021–2022)')->numeric()->required()->default(0),
                    TextInput::make('data.panel_count_ay_4')->label('No. of Times as Panel Member (AY 2022–2023)')->numeric()->required()->default(0),
                ];
                break;
            case 'mentor':
                $schema = [
                    TextInput::make('data.competition_name')->label('Name of the Academic Competition')->required()->columnSpanFull(),
                    TextInput::make('data.sponsor')->label('Name of Sponsor Organization')->required(),
                    TextInput::make('data.award_received')->label('Award Received')->required(),
                    DatePicker::make('data.date_awarded')->label('Date Awarded')->required(),
                ];
                break;
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (Evidence Link)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra1-mentor/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
