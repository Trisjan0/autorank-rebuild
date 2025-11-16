<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Application;
use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
use App\Tables\Actions\ViewSubmissionFilesAction;

class AwardsRecognitionWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a4.awards-recognition-widget';

    public function getGoogleDriveFolderPath(): array
    {
        return [$this->getKACategory(), 'C: Awards and Recognition'];
    }

    protected function getKACategory(): string
    {
        return 'KRA IV';
    }

    protected function getActiveSubmissionType(): string
    {
        return 'profdev-award-recognition';
    }

    protected function getOptionsMaps(): array
    {
        return [
            'scope' => [
                'institutional' => 'Institutional',
                'local' => 'Local',
                'regional' => 'Regional',
            ],
        ];
    }

    public function getDisplayFormattingMap(): array
    {
        return [
            'Scope' => $this->getOptionsMaps()['scope'],
            'Date Given' => 'm/d/Y',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading('Awards and Recognition')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of the Award')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn(?string $state): string => $this->getOptionsMaps()['scope'][$state] ?? Str::title($state ?? ''))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.awarding_body')->label('Award-Giving Body'),
                Tables\Columns\TextColumn::make('data.date_given')->label('Date Given')->date('m/d/Y'),
                ScoreColumn::make('score'),
            ])
            ->headerActions($this->getTableHeaderActions())
            ->actions($this->getTableActions())
            ->paginated(!$this->validation_mode)
            ->emptyStateHeading($this->getTableEmptyStateHeading())
            ->emptyStateDescription($this->getTableEmptyStateDescription());
    }

    protected function getTableQuery(): Builder
    {
        if ($this->validation_mode) {
            return Submission::query()
                ->where('application_id', $this->record->id)
                ->where('type', $this->getActiveSubmissionType());
        }

        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType())
            ->where('application_id', $this->selectedApplicationId);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.name')
                ->label('Name of the Award')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.scope')
                ->label('Scope of the Award')
                ->options($this->getOptionsMaps()['scope'])
                ->searchable()
                ->required(),
            TextInput::make('data.awarding_body')
                ->label('Award-Giving Body/Organization')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_given')
                ->label('Date the Award was Given')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),
            TextInput::make('data.venue')
                ->label('Venue of the Award Ceremony')
                ->required()
                ->maxLength(255),

            $this->getKRAFileUploadComponent(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->label('Add')
                ->form($this->getFormSchema())
                ->disabled(function () {
                    $application = Application::find($this->selectedApplicationId);
                    if (!$application) {
                        return true;
                    }
                    return $application->status !== 'Draft';
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = Auth::id();
                    $data['application_id'] = $this->selectedApplicationId;
                    $data['category'] = $this->getKACategory();
                    $data['type'] = $this->getActiveSubmissionType();
                    return $data;
                })
                ->modalHeading('Submit New Award/Recognition')
                ->modalWidth('3xl')
                ->hidden($this->validation_mode)
                ->after(fn() => $this->mount()),
        ];
    }

    protected function getTableActions(): array
    {
        if ($this->validation_mode) {
            return [
                $this->getViewFilesAction(),
                $this->getValidateSubmissionAction(),
            ];
        }

        return [
            ViewSubmissionFilesAction::make(),
            Tables\Actions\EditAction::make()
                ->form($this->getFormSchema())
                ->modalHeading('Edit Award/Recognition')
                ->modalWidth('3xl')
                ->visible($this->getActionVisibility()),
            Tables\Actions\DeleteAction::make()
                ->after(fn() => $this->mount())
                ->visible($this->getActionVisibility()),
        ];
    }
}
