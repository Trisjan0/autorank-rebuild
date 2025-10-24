<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ConferenceTrainingWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'profdev-conference-training')
            )
            ->heading('Conference/Training Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Conference/Training')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')->label('Scope')->badge(),
                Tables\Columns\TextColumn::make('data.organizer')->label('Organizer'),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Date of Activity')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Participation')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->columnSpanFull(),
            TextInput::make('data.scope')
                ->label('Scope')
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer')
                ->required(),
            DatePicker::make('data.date_activity')
                ->label('Date of Activity')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-training')
                ->columnSpanFull(),
        ];
    }
}
