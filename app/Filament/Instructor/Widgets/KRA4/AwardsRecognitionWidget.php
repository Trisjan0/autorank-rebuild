<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AwardsRecognitionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA IV')
                    ->where('type', 'profdev-award-recognition')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Awards and Recognition')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of the Award')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn(?string $state): string => Str::title($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.awarding_body')->label('Award-Giving Body'),
                Tables\Columns\TextColumn::make('data.date_given')->label('Date Given')->date(),
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
                        $data['type'] = 'profdev-award-recognition';
                        return $data;
                    })
                    ->modalHeading('Submit New Award/Recognition')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Award/Recognition')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
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
                ->options([
                    'institutional' => 'Institutional',
                    'local' => 'Local',
                    'regional' => 'Regional',
                ])
                ->required(),
            TextInput::make('data.awarding_body')
                ->label('Award-Giving Body/Organization')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_given')
                ->label('Date the Award was Given')
                ->required()
                ->maxDate(now()),
            TextInput::make('data.venue')
                ->label('Venue of the Award Ceremony')
                ->required()
                ->maxLength(255),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (e.g., Certificate, Plaque Photo)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-awards')
                ->columnSpanFull(),
        ];
    }
}
