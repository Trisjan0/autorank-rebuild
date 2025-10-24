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

class AwardsRecognitionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'profdev-award')
            )
            ->heading('Awards and Recognition Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of the Award')->wrap(),
                Tables\Columns\TextColumn::make('data.scope')->label('Scope')->badge(),
                Tables\Columns\TextColumn::make('data.award_body')->label('Award-Giving Body'),
                Tables\Columns\TextColumn::make('data.date_given')->label('Date Given')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Award')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-award';
                        return $data;
                    })
                    ->modalHeading('Submit New Award or Recognition')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Award or Recognition')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.name')
                ->label('Name of the Award')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.scope')
                ->label('Scope of the Award')
                ->required(),
            TextInput::make('data.award_body')
                ->label('Award-Giving Body/Organization')
                ->required(),
            DatePicker::make('data.date_given')
                ->label('Date the Award Was Given')
                ->required(),
            TextInput::make('data.venue')
                ->label('Venue of the Award Ceremony')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-awards')
                ->columnSpanFull(),
        ];
    }
}
