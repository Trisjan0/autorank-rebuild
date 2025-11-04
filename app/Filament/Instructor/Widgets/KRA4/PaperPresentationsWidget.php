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
use App\Tables\Columns\ScoreColumn;

class PaperPresentationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA IV')
                    ->where('type', 'profdev-paper-presentation')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Paper Presentation Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title of Paper')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn(?string $state): string => Str::title($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.conference_title')->label('Title of Conference'),
                Tables\Columns\TextColumn::make('data.date_presented')->label('Date Presented')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-paper-presentation';
                        return $data;
                    })
                    ->modalHeading('Submit New Paper Presentation')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Paper Presentation')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Paper')
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
            TextInput::make('data.conference_title')
                ->label('Title of the Conference')
                ->required()
                ->maxLength(255),
            TextInput::make('data.organizer')
                ->label('Conference Organizer')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_presented')
                ->label('Date Presented')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (e.g., Certificate of Presentation, Copy of Paper)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-papers')
                ->columnSpanFull(),
        ];
    }
}
