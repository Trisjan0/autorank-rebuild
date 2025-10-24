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

class IndustryExperienceWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'profdev-bonus-industry')
            )
            ->heading('Industry Experience Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.organization_name')->label('Name of Company/Organization')->wrap(),
                Tables\Columns\TextColumn::make('data.designation')->label('Designation/Position'),
                Tables\Columns\TextColumn::make('data.years')->label('No. of Years'),
                Tables\Columns\TextColumn::make('data.period_start')->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('data.period_end')->label('Period End')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Industry Experience')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-bonus-industry';
                        return $data;
                    })
                    ->modalHeading('Submit New Industry Experience')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Industry Experience')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.organization_name')
                ->label('Name of Company/Organization')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.designation')
                ->label('Designation/Position')
                ->required(),
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
                ->directory('proof-documents/kra4-bonus-industry')
                ->columnSpanFull(),
        ];
    }
}
