<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ConferenceTrainingWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA IV')
                    ->where('type', 'profdev-conference-training')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Conference/Training Participation')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Conference/Training')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn(?string $state): string => Str::title($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Date of Activity')->date(),
                Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-conference-training';
                        return $data;
                    })
                    ->modalHeading('Submit New Conference/Training Participation')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Conference/Training Participation')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
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
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer/Sponsoring Body')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_activity')
                ->label('Date of Activity')
                ->required()
                ->maxDate(now()),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (e.g., Certificate of Participation)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-training')
                ->columnSpanFull(),
        ];
    }
}
