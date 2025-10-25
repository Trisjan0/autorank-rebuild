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

class LiteraryPublicationWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'creative-literary-publication')
            )
            ->heading('Literary Publication Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.literary_type')
                    ->label('Type')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.publisher')->label('Publisher'),
                Tables\Columns\TextColumn::make('data.date_published')->label('Date Published')->date(),
                Tables\Columns\TextColumn::make('score')->label('Score')->numeric(2),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA II';
                        $data['type'] = 'creative-literary-publication';
                        return $data;
                    })
                    ->modalHeading('Submit New Literary Publication')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Literary Publication')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')
                ->label('Title of Literary Publication')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.literary_type')
                ->label('Type of Literary Publication')
                ->options([
                    'novel' => 'Novel',
                    'short_story' => 'Short Story',
                    'essay' => 'Essay',
                    'poetry' => 'Poetry',
                    'others' => 'Others',
                ])
                ->required(),
            TextInput::make('data.reviewer')
                ->label('Reviewer, Evaluator or Its Equivalent')
                ->maxLength(255)
                ->required(),
            TextInput::make('data.publication_name')
                ->label('Name of Publication')
                ->maxLength(255)
                ->required(),
            TextInput::make('data.publisher')
                ->label('Name of Publisher/Press')
                ->maxLength(255)
                ->required(),
            DatePicker::make('data.date_published')
                ->label('Date Published')
                ->maxDate(now())
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra2-creative-literary')
                ->columnSpanFull(),
        ];
    }
}
