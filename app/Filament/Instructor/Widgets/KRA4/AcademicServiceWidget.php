<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Forms\Components\TrimmedNumericInput;
use App\Tables\Columns\ScoreColumn;

class AcademicServiceWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA IV')
                    ->where('type', 'profdev-academic-service')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Academic Service in Higher Education')
            ->columns([
                Tables\Columns\TextColumn::make('data.hei_name')->label('Name of HEI')->wrap(),
                Tables\Columns\TextColumn::make('data.designation')
                    ->label('Designation/Position')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.no_of_years')->label('No. of Years'),
                Tables\Columns\TextColumn::make('data.period_start')->label('Period Start')->date(),
                Tables\Columns\TextColumn::make('data.period_end')->label('Period End')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA IV';
                        $data['type'] = 'profdev-academic-service';
                        return $data;
                    })
                    ->modalHeading('Submit Academic Service Record')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Academic Service Record')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.hei_name')
                ->label('Name of HEI/s')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            Select::make('data.designation')
                ->label('Designation/Position')
                ->options([
                    'president' => 'President',
                    'vp_dean_director' => 'Vice President, Dean or Director',
                    'dept_program_head' => 'Department/Program Head',
                    'faculty_member' => 'Faculty Member',
                ])
                ->required()
                ->searchable(),
            DatePicker::make('data.period_start')
                ->label('Period Covered Start')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now())
                ->live(),
            DatePicker::make('data.period_end')
                ->label('Period Covered End')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->minDate(fn(Get $get) => $get('data.period_start'))
                ->live(),
            TrimmedNumericInput::make('data.no_of_years')
                ->label('Number of Years')
                ->step('0.01')
                ->required()
                ->minValue(0)
                ->helperText('Please ensure this matches the duration from the Period Covered dates.')
                ->rules([
                    function (Get $get) {
                        return function (string $attribute, $value, Closure $fail) use ($get) {
                            $startDateStr = $get('data.period_start');
                            $endDateStr = $get('data.period_end');
                            $enteredYears = (float) $value;

                            if ($startDateStr && $endDateStr) {
                                try {
                                    $startDate = Carbon::parse($startDateStr);
                                    $endDate = Carbon::parse($endDateStr);

                                    $calculatedYears = $startDate->diffInDays($endDate->addDay()) / 365.25;

                                    $tolerance = 0.02;

                                    if (abs($calculatedYears - $enteredYears) > $tolerance) {
                                        $fail("The 'Number of Years' entered ({$enteredYears}) does not closely match the calculated duration (" . number_format($calculatedYears, 2) . " years) based on the start and end dates.");
                                    }
                                } catch (\Exception $e) {
                                }
                            }
                        };
                    }
                ]),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (e.g., Service Record, Contract)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-acadservice')
                ->columnSpanFull(),
        ];
    }
}
