<?php

namespace App\Filament\Instructor\Widgets\KRA3;

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
use App\Forms\Components\TrimmedNumericInput;
use App\Tables\Columns\ScoreColumn;
use Filament\Forms\Get;

class IncomeGenerationWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'extension-income-generation')
            )
            ->heading('Contribution to Income Generation Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Product/Project')->wrap(),
                Tables\Columns\TextColumn::make('data.role')
                    ->label('Role')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.amount')
                    ->label('Total Amount')
                    ->numeric(decimalPlaces: 2)
                    ->prefix('₱'),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-income-generation';
                        return $data;
                    })
                    ->modalHeading('Submit New Income Generation Contribution')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Income Generation Contribution')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.name')
                ->label('Name of the Commercialized Product, Funded Project, or Project with Industry')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
            Select::make('data.role')
                ->label('Role')
                ->options([
                    'lead_contributor' => 'Lead Contributor',
                    'co_contributor' => 'Co-contributor',
                ])
                ->searchable()
                ->required(),
            TrimmedNumericInput::make('data.amount')
                ->label('Total Amount (Actual Income)')
                ->prefix('₱')
                ->required()
                ->minValue(0),
            DatePicker::make('data.coverage_start')
                ->label('Coverage Period Start')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now())
                ->live(),
            DatePicker::make('data.coverage_end')
                ->label('Coverage Period End')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->minDate(fn(Get $get) => $get('data.coverage_start')),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-income')
                ->columnSpanFull(),
        ];
    }
}
