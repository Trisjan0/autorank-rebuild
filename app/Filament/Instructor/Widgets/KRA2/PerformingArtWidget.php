<?php

namespace App\Filament\Instructor\Widgets\KRA2;

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
use App\Tables\Columns\ScoreColumn;
use Filament\Forms\Get;

class PerformingArtWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'creative-performing-art')
            )
            ->heading('Performing Art Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.art_type')
                    ->label('Art Type')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.classification')
                    ->label('Classification')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title()),
                Tables\Columns\TextColumn::make('data.date_performed')->label('Date Performed')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null;
                        $data['category'] = 'KRA II';
                        $data['type'] = 'creative-performing-art';
                        return $data;
                    })
                    ->modalHeading('Submit New Creative Performing Artwork')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Creative Performing Artwork')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Creative Performing Art')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.art_type')
                ->label('Type of Performing Art')
                ->options([
                    'song_music' => 'Song/Music',
                    'choreography_dance' => 'Choreography/Dance',
                    'drama_theater' => 'Drama/Theater',
                    'others' => 'Others',
                ])
                ->searchable()
                ->required(),
            Select::make('data.classification')
                ->label('Classification')
                ->options([
                    'new_creation' => 'New Creation',
                    'own_work' => 'Own Work',
                    'work_of_others' => 'Work of Others',
                ])
                ->searchable()
                ->required()
                ->live(),
            DatePicker::make('data.date_performed')
                ->label('Date Copyrighted / Date Performed')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->maxDate(now())
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of Performance')
                ->maxLength(255)
                ->required()
                ->hidden(fn(Get $get): bool => $get('data.classification') === 'new_creation'),
            TextInput::make('data.organizer')
                ->label('Organizer of the Event (or Publisher, if applicable)')
                ->maxLength(255)
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra2-creative-performing')
                ->columnSpanFull(),
        ];
    }
}
