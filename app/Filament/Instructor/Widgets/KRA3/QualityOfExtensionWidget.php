<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class QualityOfExtensionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function submissionExists(): bool
    {
        $activeApplicationId = Auth::user()->activeApplication->id ?? null;

        if (!$activeApplicationId) {
            return true;
        }

        return Submission::where('user_id', Auth::id())
            ->where('application_id', $activeApplicationId)
            ->where('type', 'extension-quality-rating')
            ->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'extension-quality-rating')
                    ->where('application_id', Auth::user()->activeApplication->id ?? null)
            )
            ->heading('Client Satisfaction Rating Submission')
            ->columns([
                Tables\Columns\TextColumn::make('data.first_sem_rating')->label('1st Sem Rating'),
                Tables\Columns\TextColumn::make('data.second_sem_rating')->label('2nd Sem Rating'),
                Tables\Columns\TextColumn::make('data.semesters_deducted')->label('Semesters Deducted'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Rating')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-quality-rating';
                        return $data;
                    })
                    ->modalHeading('Submit Client Satisfaction Rating')
                    ->modalWidth('3xl')
                    ->hidden(fn(): bool => $this->submissionExists()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Client Satisfaction Rating')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.first_sem_rating')
                ->label('1st Semester Client Satisfaction Rating')
                ->numeric()
                ->required(),
            FileUpload::make('data.first_sem_evidence')
                ->label('Link to Evidence from Google Drive (1st Semester)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-quality-sem1')
                ->columnSpanFull(),

            TextInput::make('data.second_sem_rating')
                ->label('2nd Semester Client Satisfaction Rating')
                ->numeric()
                ->required(),
            FileUpload::make('data.second_sem_evidence')
                ->label('Link to Evidence from Google Drive (2nd Semester)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-quality-sem2')
                ->columnSpanFull(),

            TextInput::make('data.semesters_deducted')
                ->label('Specify Number of Semesters Deducted from the Divisor')
                ->numeric()
                ->default(0)
                ->required(),
            Textarea::make('data.deduction_reason')
                ->label('Reason for Deducting the Divisor')
                ->default('Not Applicable')
                ->required()
                ->columnSpanFull(),
        ];
    }
}
