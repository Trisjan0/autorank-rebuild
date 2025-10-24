<?php

namespace App\Filament\Instructor\Widgets\KRA3;

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

class ProfessionalServicesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a3.professional-services-widget';

    public ?string $activeTable = 'accreditation_services';

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
        switch ($this->activeTable) {
            case 'accreditation_services':
                return [
                    Tables\Columns\TextColumn::make('data.agency_name')->label('Agency/Organization')->wrap(),
                    Tables\Columns\TextColumn::make('data.services_provided')->label('Services Provided'),
                    Tables\Columns\TextColumn::make('data.scope')->label('Scope')->badge(),
                ];
            case 'judge_examiner':
                return [
                    Tables\Columns\TextColumn::make('data.event_title')->label('Title of Event/Activity')->wrap(),
                    Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                    Tables\Columns\TextColumn::make('data.event_date')->label('Date of Event')->date(),
                ];
            case 'consultant':
                return [
                    Tables\Columns\TextColumn::make('data.project_title')->label('Title of Project')->wrap(),
                    Tables\Columns\TextColumn::make('data.organization_name')->label('Organization'),
                    Tables\Columns\TextColumn::make('data.scope')->label('Scope')->badge(),
                    Tables\Columns\TextColumn::make('data.role')->label('Role'),
                ];
            case 'media_service':
                return [
                    Tables\Columns\TextColumn::make('data.service')->label('Service')->wrap(),
                    Tables\Columns\TextColumn::make('data.media_name')->label('Name of Media'),
                    Tables\Columns\TextColumn::make('data.program_title')->label('Program Title'),
                ];
            case 'training_resource_person':
                return [
                    Tables\Columns\TextColumn::make('data.training_title')->label('Title of Training')->wrap(),
                    Tables\Columns\TextColumn::make('data.participation_type')->label('Participation')->badge(),
                    Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                    Tables\Columns\TextColumn::make('data.scope')->label('Scope')->badge(),
                    Tables\Columns\TextColumn::make('data.total_hours')->label('Total Hours'),
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
                    $data['category'] = 'KRA III';
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

        switch ($this->activeTable) {
            case 'accreditation_services':
                $schema = [
                    TextInput::make('data.agency_name')->label('Name of Agency/Organization')->required()->columnSpanFull(),
                    DatePicker::make('data.period_start')->label('Appointment Period Start')->required(),
                    DatePicker::make('data.period_end')->label('Appointment Period End')->required(),
                    Textarea::make('data.services_provided')->label('QA-related Services Provided')->required()->columnSpanFull(),
                    TextInput::make('data.scope')->label('Scope')->required(),
                    TextInput::make('data.deployment_count')->label('No. of Deployment')->numeric()->required(),
                ];
                break;
            case 'judge_examiner':
                $schema = [
                    Textarea::make('data.event_title')->label('Title of the Event/Activity')->required()->columnSpanFull(),
                    TextInput::make('data.organizer')->label('Organizer')->required(),
                    DatePicker::make('data.event_date')->label('Date of Event')->required(),
                    TextInput::make('data.award_nature')->label('Nature of the Award')->required(),
                    TextInput::make('data.venue')->label('Venue')->required(),
                ];
                break;
            case 'consultant':
                $schema = [
                    Textarea::make('data.project_title')->label('Title of the Project/Consultancy')->required()->columnSpanFull(),
                    TextInput::make('data.organization_name')->label('Name of Organization')->required()->columnSpanFull(),
                    DatePicker::make('data.period_start')->label('Period of Engagement Start')->required(),
                    DatePicker::make('data.period_end')->label('Period of Engagement End')->required(),
                    TextInput::make('data.scope')->label('Scope')->required(),
                    TextInput::make('data.role')->label('Role')->required(),
                ];
                break;
            case 'media_service':
                $schema = [
                    Textarea::make('data.service')->label('Services')->required()->columnSpanFull(),
                    TextInput::make('data.media_name')->label('Name of Media')->required(),
                    TextInput::make('data.program_title')->label('Title of Newspaper Column or TV/Radio Program')->required(),
                    DatePicker::make('data.period_start')->label('Period of Engagement Start')->required(),
                    DatePicker::make('data.period_end')->label('Period of Engagement End')->required(),
                    TextInput::make('data.engagement_count')->label('No. of Engagements')->numeric()->required(),
                ];
                break;
            case 'training_resource_person':
                $schema = [
                    Textarea::make('data.training_title')->label('Title of the Training')->required()->columnSpanFull(),
                    TextInput::make('data.participation_type')->label('Type of Participation')->required(),
                    TextInput::make('data.organizer')->label('Organizer')->required(),
                    DatePicker::make('data.period_start')->label('Period of Engagement Start')->required(),
                    DatePicker::make('data.period_end')->label('Period of Engagement End')->required(),
                    TextInput::make('data.scope')->label('Scope')->required(),
                    TextInput::make('data.total_hours')->label('Total No. of Hours')->numeric()->required(),
                ];
                break;
        }

        $schema[] = FileUpload::make('google_drive_file_id')
            ->label('Proof Document(s) (Evidence Link)')
            ->multiple()
            ->reorderable()
            ->required()
            ->disk('private')
            ->directory(fn(): string => 'proof-documents/kra3-prof/' . $this->activeTable)
            ->columnSpanFull();

        return $schema;
    }
}
