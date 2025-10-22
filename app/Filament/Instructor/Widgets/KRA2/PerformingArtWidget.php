<?php

namespace App\Filament\Instructor\Widgets\KRA2;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PerformingArtWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'creative-performing-art')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.art_type')->label('Art Type')->badge(),
                Tables\Columns\TextColumn::make('data.classification')->label('Classification'),
                Tables\Columns\TextColumn::make('data.date_performed')->label('Date Performed')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->columnSpanFull(),
            TextInput::make('data.art_type')
                ->label('Type of Performing Art (e.g., Music, Dance, Theatre)')
                ->required(),
            TextInput::make('data.classification')
                ->label('Classification')
                ->required(),
            DatePicker::make('data.date_performed')
                ->label('Date Copyrighted / Date Performed')
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of Performance')
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer of the Event (or Publisher, if applicable)')
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
