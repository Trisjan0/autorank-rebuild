<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

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
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Product/Project')->wrap(),
                Tables\Columns\TextColumn::make('data.role')->label('Role')->badge(),
                Tables\Columns\TextColumn::make('data.amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->prefix('₱'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->columnSpanFull(),
            TextInput::make('data.role')
                ->label('Role')
                ->required(),
            TextInput::make('data.amount')
                ->label('Total Amount')
                ->numeric()
                ->prefix('₱')
                ->required(),
            DatePicker::make('data.coverage_start')
                ->label('Coverage Period Start')
                ->required(),
            DatePicker::make('data.coverage_end')
                ->label('Coverage Period End')
                ->required(),
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
