<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class AcademicServiceWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'profdev-bonus-academic')
            )
            ->heading('Academic Service Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.designation')->label('Designation/Position')->wrap(),
                Tables\Columns\TextColumn::make('data.hei_name')->label('Name of HEI'),
                Tables\Columns\TextColumn::make('data.years')->label('No. of Years'),
                Tables\Columns\TextColumn::make('data.period_start')->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('data.period_end')->label('Period End')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Academic Service')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-bonus-academic';
                        return $data;
                    })
                    ->modalHeading('Submit New Academic Service')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Academic Service')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.designation')
                ->label('Designation/Position')
                ->required(),
            TextInput::make('data.hei_name')
                ->label('Name of HEI/s')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.years')
                ->label('No. of Years')
                ->numeric()
                ->required(),
            DatePicker::make('data.period_start')
                ->label('Period Covered Start')
                ->required(),
            DatePicker::make('data.period_end')
                ->label('Period Covered End')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-bonus-academic')
                ->columnSpanFull(),
        ];
    }
}
