<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use App\Filament\Instructor\Widgets\BaseKRAWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Tables\Columns\ScoreColumn;
use App\Filament\Traits\HandlesKRAFileUploads;

class ConferenceTrainingWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a4.conference-training-widget';

    protected function getGoogleDriveFolderPath(): array
    {
        return [$this->getKACategory(), 'B: Conference and Training'];
    }

    protected function getKACategory(): string
    {
        return 'KRA IV';
    }

    protected function getActiveSubmissionType(): string
    {
        return 'profdev-conference-training';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading('Conference/Training Participation')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Conference/Training')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn(?string $state): string => Str::title($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Date of Activity')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions());
    }

    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('category', $this->getKACategory())
            ->where('type', $this->getActiveSubmissionType())
            ->where('application_id', $this->selectedApplicationId);
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = $this->selectedApplicationId;
                    $data['category'] = $this->getKACategory();
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading('Submit New Conference/Training Participation')
                ->modalWidth('3xl')
                ->after(fn() => $this->mount()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading('Edit Conference/Training Participation')
                ->modalWidth('3xl')
                ->visible($this->getActionVisibility()),
            Tables\Actions\DeleteAction::make()
                ->after(fn() => $this->mount())
                ->visible($this->getActionVisibility()),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.name')
                ->label('Name of Conference/Training')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
            Select::make('data.scope')
                ->label('Scope')
                ->options([
                    'local' => 'Local',
                    'international' => 'International',
                ])
                ->searchable()
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer/Sponsoring Body')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_activity')
                ->label('Date of Activity')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),

            $this->getKRAFileUploadComponent(),
        ];
    }
}
