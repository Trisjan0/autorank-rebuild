<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class BonusCriterionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'extension-bonus-designation')
            )
            ->heading('Administrative Designations')
            ->columns([
                Tables\Columns\TextColumn::make('data.designation')->label('Designation')->wrap(),
                Tables\Columns\TextColumn::make('data.period_start')->label('Effectivity Start')->date(),
                Tables\Columns\TextColumn::make('data.period_end')->label('Effectivity End')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Designation')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-bonus-designation';
                        return $data;
                    })
                    ->modalHeading('Submit Administrative Designation')
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Administrative Designation')
                    ->modalWidth('2xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.designation')
                ->label('Designation')
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.period_start')
                ->label('Effectivity Period Start')
                ->required(),
            DatePicker::make('data.period_end')
                ->label('Effectivity Period End')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-bonus')
                ->columnSpanFull(),
        ];
    }
}
