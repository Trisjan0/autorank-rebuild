<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Tables\Columns\ScoreColumn;

class BonusCriterionWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA III')
                    ->where('type', 'extension-bonus-designation')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Administrative Designations')
            ->columns([
                Tables\Columns\TextColumn::make('data.designation')
                    ->label('Designation')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge()
                    ->wrap(),
                Tables\Columns\TextColumn::make('data.period_start')->label('Effectivity Start')->date(),
                Tables\Columns\TextColumn::make('data.period_end')->label('Effectivity End')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null;
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
            Select::make('data.designation')
                ->label('Designation')
                ->options([
                    'president_oic' => 'President or OIC President',
                    'vice_president' => 'Vice-President',
                    'chancellor' => 'Chancellor',
                    'vice_chancellor' => 'Vice-Chancellor',
                    'campus_director' => 'Campus Director/Administrator/Head',
                    'faculty_regent' => 'Faculty Regent',
                    'office_director' => 'Office Director',
                    'university_college_secretary' => 'University/College Secretary',
                    'dean' => 'Dean',
                    'associate_dean' => 'Associate Dean',
                    'project_head_kra3d' => 'Project Head',
                    'department_head' => 'Department Head',
                    'institution_committee_chair' => 'Institution-level Committee Chair',
                    'institution_committee_member' => 'Institution-level Committee Member',
                    'college_secretary' => 'College Secretary',
                    'program_chair' => 'Program Chair/Project Head',
                    'department_committee_chair' => 'Department-level Committee Chair',
                    'department_committee_member' => 'Department-level Committee Member',
                ])
                ->required()
                ->searchable()
                ->columnSpanFull(),
            DatePicker::make('data.period_start')
                ->label('Effectivity Period Start')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now())
                ->live(),
            DatePicker::make('data.period_end')
                ->label('Effectivity Period End')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->minDate(fn(Get $get) => $get('data.period_start')),
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
