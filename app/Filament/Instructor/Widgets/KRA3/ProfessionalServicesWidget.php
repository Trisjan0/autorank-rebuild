<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

class ProfessionalServicesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.instructor.widgets.k-r-a3.professional-services-widget';

    public ?string $activeTable = 'accreditation_services';

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
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('category', 'KRA III')
            ->where('type', $this->getActiveSubmissionType());
    }

    protected function getActiveSubmissionType(): string
    {
        return match ($this->activeTable) {
            'accreditation_services' => 'accreditation_services',
            'judge_examiner' => 'judge_examiner',
            'consultant' => 'consultant',
            'media_service' => 'media_service',
            'training_resource_person' => 'training_resource_person',
            default => 'unknown',
        };
    }


    protected function getTableHeading(): string
    {
        return Str::of($this->activeTable)->replace('_', ' ')->title() . ' Submissions';
    }

    protected function getTableColumns(): array
    {
        $columns = [];
        switch ($this->activeTable) {
            case 'accreditation_services':
                $columns = [
                    Tables\Columns\TextColumn::make('data.agency_name')->label('Agency/Organization')->wrap(),
                    Tables\Columns\TextColumn::make('data.services_provided')->label('Services Provided'),
                    Tables\Columns\TextColumn::make('data.scope')
                        ->label('Scope')
                        ->formatStateUsing(fn(?string $state): string => Str::title($state))
                        ->badge(),
                    Tables\Columns\TextColumn::make('data.deployment_count')->label('No. of Days'),
                    Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
                ];
                break;
            case 'judge_examiner':
                $columns = [
                    Tables\Columns\TextColumn::make('data.event_title')->label('Title of Event/Activity')->wrap(),
                    Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                    Tables\Columns\TextColumn::make('data.event_date')->label('Date of Event')->date(),
                    Tables\Columns\TextColumn::make('data.award_nature')
                        ->label('Nature')
                        ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                        ->badge(),
                    Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
                ];
                break;
            case 'consultant':
                $columns = [
                    Tables\Columns\TextColumn::make('data.project_title')->label('Title of Project')->wrap(),
                    Tables\Columns\TextColumn::make('data.organization_name')->label('Organization'),
                    Tables\Columns\TextColumn::make('data.scope')
                        ->label('Scope')
                        ->formatStateUsing(fn(?string $state): string => Str::title($state))
                        ->badge(),
                    Tables\Columns\TextColumn::make('data.role')->label('Role'),
                    Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
                ];
                break;
            case 'media_service':
                $columns = [
                    Tables\Columns\TextColumn::make('data.service')
                        ->label('Service')
                        ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                        ->badge()
                        ->wrap(),
                    Tables\Columns\TextColumn::make('data.media_name')->label('Name of Media'),
                    Tables\Columns\TextColumn::make('data.program_title')->label('Program Title'),
                    Tables\Columns\TextColumn::make('data.engagement_count')->label('Engagements')
                        ->visible(function ($record): bool {
                            if (!$record || !isset($record->data['service'])) {
                                return false;
                            }
                            return in_array($record->data['service'], ['writer_occasional_newspaper', 'guest_technical_expert']);
                        }),
                    Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
                ];
                break;
            case 'training_resource_person':
                $columns = [
                    Tables\Columns\TextColumn::make('data.training_title')->label('Title of Training')->wrap(),
                    Tables\Columns\TextColumn::make('data.participation_type')->label('Participation')->badge(),
                    Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                    Tables\Columns\TextColumn::make('data.scope')
                        ->label('Scope')
                        ->formatStateUsing(fn(?string $state): string => Str::title($state))
                        ->badge(),
                    Tables\Columns\TextColumn::make('data.total_hours')->label('Total Hours'),
                    Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
                ];
                break;
            default:
                return [];
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
                    $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                    $data['category'] = 'KRA III';
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
        $schema = match ($this->activeTable) {
            'accreditation_services' => $this->getAccreditationFormSchema(),
            'judge_examiner' => $this->getJudgeExaminerFormSchema(),
            'consultant' => $this->getConsultantFormSchema(),
            'media_service' => $this->getMediaServiceFormSchema(),
            'training_resource_person' => $this->getTrainingResourcePersonFormSchema(),
            default => [],
        };

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

    protected function getAccreditationFormSchema(): array
    {
        return [
            TextInput::make('data.agency_name')->label('Name of Agency/Organization')->required()->maxLength(255)->columnSpanFull(),
            DatePicker::make('data.period_start')->label('Appointment Period Start')->required()->maxDate(now()),
            DatePicker::make('data.period_end')->label('Appointment Period End')->required()->minDate(fn(Get $get) => $get('data.period_start')),
            Textarea::make('data.services_provided')->label('QA-related Services Provided')->required()->maxLength(65535)->columnSpanFull(),
            Select::make('data.scope')->label('Scope')
                ->options(['local' => 'Local', 'international' => 'International'])
                ->required(),
            TextInput::make('data.deployment_count')->label('No. of Days Rendered')->integer()->required()->minValue(1),
        ];
    }

    protected function getJudgeExaminerFormSchema(): array
    {
        return [
            Textarea::make('data.event_title')->label('Title of the Event/Activity')->required()->maxLength(65535)->columnSpanFull(),
            TextInput::make('data.organizer')->label('Organizer/Sponsor')->required()->maxLength(255),
            DatePicker::make('data.event_date')->label('Date of Event')->required()->maxDate(now()),
            Select::make('data.award_nature')->label('Nature of the Award')
                ->options([
                    'research_award' => 'Research Award',
                    'academic_competition' => 'Academic Competition',
                ])
                ->required(),
            TextInput::make('data.venue')->label('Venue')->required()->maxLength(255),
            TextInput::make('data.role')->label('Role (e.g., Judge, Examiner)')->required()->maxLength(255),
        ];
    }

    protected function getConsultantFormSchema(): array
    {
        return [
            Textarea::make('data.project_title')->label('Title of the Project/Consultancy')->required()->maxLength(65535)->columnSpanFull(),
            TextInput::make('data.organization_name')->label('Name of Organization/Sponsoring Body')->required()->maxLength(255)->columnSpanFull(),
            DatePicker::make('data.period_start')->label('Period of Engagement Start')->required()->maxDate(now()),
            DatePicker::make('data.period_end')->label('Period of Engagement End')->required()->minDate(fn(Get $get) => $get('data.period_start')),
            Select::make('data.scope')->label('Scope')
                ->options(['local' => 'Local', 'international' => 'International'])
                ->required(),
            TextInput::make('data.role')->label('Role')->required()->maxLength(255),
        ];
    }

    protected function getMediaServiceFormSchema(): array
    {
        return [
            Select::make('data.service')->label('Service Rendered')
                ->options([
                    'writer_occasional_newspaper' => 'Writer of Occasional Newspaper Column/Magazine Article',
                    'writer_regular_newspaper' => 'Writer of Regular Newspaper Column/Magazine Article',
                    'host_tv_radio_program' => 'Host of TV/Radio Program',
                    'guest_technical_expert' => 'Guesting as Technical Expert for TV or Radio',
                ])
                ->required()
                ->live()
                ->columnSpanFull(),
            TextInput::make('data.media_name')->label('Name of Media (Newspaper/Magazine/Station/Network)')->required()->maxLength(255),
            TextInput::make('data.program_title')->label('Title of Newspaper Column or TV/Radio Program')->required()->maxLength(255),
            DatePicker::make('data.period_start')->label('Date/Period Started')->required()->maxDate(now()),
            DatePicker::make('data.period_end')->label('Date/Period Ended')
                ->required()
                ->minDate(fn(Get $get) => $get('data.period_start'))
                ->visible(fn(Get $get): bool => $get('data.service') === 'writer_regular_newspaper' || $get('data.service') === 'host_tv_radio_program'),
            TextInput::make('data.engagement_count')->label('No. of Engagements/Guestings')
                ->integer()
                ->required()
                ->minValue(1)
                ->visible(fn(Get $get): bool => $get('data.service') === 'writer_occasional_newspaper' || $get('data.service') === 'guest_technical_expert'),
        ];
    }

    protected function getTrainingResourcePersonFormSchema(): array
    {
        return [
            Textarea::make('data.training_title')->label('Title of the Training/Course/Seminar/Workshop')->required()->maxLength(65535)->columnSpanFull(),
            Select::make('data.participation_type')->label('Type of Participation/Role')
                ->options([
                    'resource_person' => 'Resource Person',
                    'convenor' => 'Convenor',
                    'facilitator' => 'Facilitator',
                    'moderator' => 'Moderator',
                    'keynote_speaker' => 'Keynote/Plenary Speaker',
                    'panelist' => 'Panelist',
                    'other' => 'Other',
                ])
                ->required(),
            TextInput::make('data.organizer')->label('Organizer/Sponsoring Body')->required()->maxLength(255),
            DatePicker::make('data.period_start')->label('Date Conducted/Start Date')->required()->maxDate(now()),
            DatePicker::make('data.period_end')->label('End Date (if applicable)')->minDate(fn(Get $get) => $get('data.period_start')),
            Select::make('data.scope')->label('Scope')
                ->options(['local' => 'Local', 'international' => 'International'])
                ->required(),
            TextInput::make('data.total_hours')->label('Total No. of Hours')->integer()->required()->minValue(1),
        ];
    }
}
