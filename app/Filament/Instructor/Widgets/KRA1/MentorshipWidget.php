<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class MentorshipWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'mentorship-competition')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.competition_name')->label('Competition Name')->wrap(),
                Tables\Columns\TextColumn::make('data.award')->label('Award Received')->badge(),
                Tables\Columns\TextColumn::make('data.date_awarded')->label('Date Awarded')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'mentorship-competition';
                        return $data;
                    })
                    ->modalHeading('Submit Mentorship Service (Competition)')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form($this->getFormSchema())->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.competition_name')
                ->label('Name of Academic Competition')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.sponsor')
                ->label('Name of Sponsor Organization')
                ->required(),
            TextInput::make('data.award')
                ->label('Award Received')
                ->required(),
            DatePicker::make('data.date_awarded')
                ->label('Date Awarded')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/mentorship')
                ->columnSpanFull(),
        ];
    }
}
