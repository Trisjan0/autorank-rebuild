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
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.creative_type')->label('Creative Type')->badge(),
                Tables\Columns\TextColumn::make('data.classification')->label('Classification'),
                Tables\Columns\TextColumn::make('data.date_exhibited')->label('Exhibition Date')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->columnSpanFull(),
            TextInput::make('data.creative_type')
                ->label('Type of Creative Work (e.g., Visual Art, Film)')
                ->required(),
            TextInput::make('data.classification')
                ->label('Classification')
                ->required(),
            DatePicker::make('data.date_exhibited')
                ->label('Exhibition Date')
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of Exhibit')
                ->required(),
            TextInput::make('data.organizer')
                ->label('Organizer of the Event')
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
