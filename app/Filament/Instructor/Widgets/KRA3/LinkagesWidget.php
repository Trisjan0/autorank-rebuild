<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Instructor\Widgets\BaseKRAWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Tables\Columns\ScoreColumn;
use Filament\Forms\Get;
use App\Filament\Traits\HandlesKRAFileUploads;

class LinkagesWidget extends BaseKRAWidget
{
    use HandlesKRAFileUploads;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.instructor.widgets.k-r-a3.linkages-widget';

    protected function getGoogleDriveFolderPath(): array
    {
        return [$this->getKACategory(), 'A: Service to the Institution', 'Linkages'];
    }

    protected function getKACategory(): string
    {
        return 'KRA III';
    }

    protected function getActiveSubmissionType(): string
    {
        return 'extension-linkage';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => $this->getTableQuery())
            ->heading('Linkages, Networking and Partnership Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.partner_name')->label('Name of Partner')->wrap(),
                Tables\Columns\TextColumn::make('data.faculty_role')
                    ->label('Faculty Role')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.moa_start')->label('MOA Start')->date(),
                Tables\Columns\TextColumn::make('data.moa_expiration')->label('MOA Expiration')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
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
                    ->modalHeading('Submit New Linkage/Partnership')
                    ->modalWidth('3xl')
                    ->after(fn() => $this->mount()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Linkage/Partnership')
                    ->modalWidth('3xl')
                    ->visible($this->getActionVisibility()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn() => $this->mount())
                    ->visible($this->getActionVisibility()),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Submission::query()
            ->where('user_id', Auth::id())
            ->where('type', $this->getActiveSubmissionType())
            ->where('application_id', $this->selectedApplicationId);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.partner_name')
                ->label('Name of Partner')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('data.nature')
                ->label('Nature of Partnership')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
            Select::make('data.faculty_role')
                ->label('Faculty Role in the Forging of Partnership')
                ->options([
                    'lead_coordinator' => 'Lead Coordinator',
                    'assistant_coordinator' => 'Assistant Coordinator',
                ])
                ->searchable()
                ->required(),
            DatePicker::make('data.moa_start')
                ->label('MOA Start Date')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now())
                ->live(),
            DatePicker::make('data.moa_expiration')
                ->label('MOA Expiration Date')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->minDate(fn(Get $get) => $get('data.moa_start')),
            Textarea::make('data.activities')
                ->label('Activities Conducted Based on MOA')
                ->helperText('Not necessarily involving the faculty.')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
            DatePicker::make('data.activity_date')
                ->label('Date of Activity')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),

            $this->getKRAFileUploadComponent(),
        ];
    }
}
