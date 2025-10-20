<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class TeachingEffectivenessWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'teaching-effectiveness')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submission Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data.student_evaluations')
                    ->label('Student Semesters')
                    ->formatStateUsing(fn($state) => count($state) . ' submitted'),
                Tables\Columns\TextColumn::make('data.supervisor_evaluations')
                    ->label('Supervisor Semesters')
                    ->formatStateUsing(fn($state) => count($state) . ' submitted'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'teaching-effectiveness';
                        $data['google_drive_file_id'] = ['See JSON data for individual file paths.'];
                        return $data;
                    })
                    ->modalHeading('Submit Teaching Effectiveness')
                    ->modalWidth('4xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Teaching Effectiveness')
                    ->modalWidth('4xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('1.1 Student Evaluation (60%)')
                ->schema([
                    Repeater::make('data.student_evaluations')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('academic_year')->label('Academic Year')->placeholder('YYYY-YYYY')->required(),
                            Select::make('semester')->options(['1st' => '1st Semester', '2nd' => '2nd Semester'])->required(),
                            TextInput::make('score')->label('Evaluation Score')->numeric()->required(),
                            FileUpload::make('proof')->label('Proof Document')->disk('private')->directory('proof-documents/teaching-effectiveness')->required(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(4),
                ]),

            Section::make('1.2 Supervisor’s Evaluation (40%)')
                ->schema([
                    Repeater::make('data.supervisor_evaluations')
                        ->hiddenLabel()
                        ->schema([
                            TextInput::make('academic_year')->label('Academic Year')->placeholder('YYYY-YYYY')->required(),
                            Select::make('semester')->options(['1st' => '1st Semester', '2nd' => '2nd Semester'])->required(),
                            TextInput::make('score')->label('Evaluation Score')->numeric()->required(),
                            FileUpload::make('proof')->label('Proof Document')->disk('private')->directory('proof-documents/teaching-effectiveness')->required(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columns(4),
                ]),
        ];
    }
}
