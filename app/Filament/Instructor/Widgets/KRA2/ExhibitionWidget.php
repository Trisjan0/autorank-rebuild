<?php

namespace App\Filament\Instructor\Widgets\KRA2;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Tables\Columns\ScoreColumn;

class ExhibitionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'creative-exhibition')
            )
            ->heading('Exhibition Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.classification')
                    ->label('Classification')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.creative_type')
                    ->label('Creative Type')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.date_exhibited')->label('Exhibition Date')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA II';
                        $data['type'] = 'creative-exhibition';
                        return $data;
                    })
                    ->modalHeading('Submit New Creative Work (Exhibition)')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Creative Work (Exhibition)')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Creative Work')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.classification')
                ->label('Classification')
                ->options([
                    'visual_arts' => 'Visual Arts',
                    'architecture' => 'Architecture',
                    'film' => 'Film',
                    'multimedia' => 'Multimedia',
                ])
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(fn(callable $set) => $set('data.creative_type', null)),
            Select::make('data.creative_type')
                ->label('Type of Creative Work')
                ->options(function (Get $get): array {
                    return match ($get('data.classification')) {
                        'visual_arts' => [
                            'painting_drawing' => 'Painting/Drawing',
                            'sculpture' => 'Sculpture',
                            'photography' => 'Photography',
                            'others' => 'Others'
                        ],
                        'architecture' => [
                            'architectural_design' => 'Architectural Design',
                            'others' => 'Others'
                        ],
                        'film' => [
                            'film_short_film' => 'Film/Short Film',
                            'others' => 'Others'
                        ],
                        'multimedia' => [
                            'multimedia' => 'Multimedia',
                            'others' => 'Others'
                        ],
                        default => [],
                    };
                })
                ->searchable()
                ->required()
                ->visible(fn(Get $get): bool => !empty($get('data.classification'))),
            DatePicker::make('data.date_exhibited')
                ->label('Exhibition Date')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->maxDate(now())
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of Exhibit')
                ->maxLength(255)
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer of the Event')
                ->maxLength(255)
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra2-creative-exhibit')
                ->columnSpanFull(),
        ];
    }
}
