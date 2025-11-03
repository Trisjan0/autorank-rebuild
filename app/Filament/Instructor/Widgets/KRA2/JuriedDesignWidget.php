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

class JuriedDesignWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'creative-juried-design')
            )
            ->heading('Juried Design Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.classification')
                    ->label('Classification')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.reviewer')->label('Reviewer/Evaluator'),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Activity Date')->date(),
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
                        $data['type'] = 'creative-juried-design';
                        return $data;
                    })
                    ->modalHeading('Submit New Juried Design')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Juried Design')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Design')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.classification')
                ->label('Classification')
                ->options([
                    'architecture' => 'Architecture',
                    'engineering' => 'Engineering',
                    'industrial_design' => 'Industrial Design',
                ])
                ->searchable()
                ->required(),
            TextInput::make('data.reviewer')
                ->label('Reviewer, Evaluator or Its Equivalent')
                ->maxLength(255)
                ->required(),
            DatePicker::make('data.date_activity')
                ->label('Activity/Exhibition Date')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->maxDate(now())
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of Activity/Exhibit')
                ->maxLength(255)
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer')
                ->maxLength(255)
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra2-creative-juried')
                ->columnSpanFull(),
        ];
    }
}
