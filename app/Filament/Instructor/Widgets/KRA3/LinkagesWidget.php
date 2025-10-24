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

class LinkagesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'extension-linkage')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.partner_name')->label('Name of Partner')->wrap(),
                Tables\Columns\TextColumn::make('data.faculty_role')->label('Faculty Role'),
                Tables\Columns\TextColumn::make('data.moa_start')->label('MOA Start')->date(),
                Tables\Columns\TextColumn::make('data.moa_expiration')->label('MOA Expiration')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-linkage';
                        return $data;
                    })
                    ->modalHeading('Submit New Linkage/Partnership')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Linkage/Partnership')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.partner_name')
                ->label('Name of Partner')
                ->required()
                ->columnSpanFull(),
            Textarea::make('data.nature')
                ->label('Nature of Partnership')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.faculty_role')
                ->label('Faculty Role in the Forging of Partnership')
                ->required(),
            DatePicker::make('data.moa_start')
                ->label('MOA Start Date')
                ->required(),
            DatePicker::make('data.moa_expiration')
                ->label('MOA Expiration Date')
                ->required(),
            Textarea::make('data.activities')
                ->label('Activities Conducted Based on MOA')
                ->helperText('Not necessarily involving the faculty.')
                ->required()
                ->columnSpanFull(),
            DatePicker::make('data.activity_date')
                ->label('Date of Activity')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-linkages')
                ->columnSpanFull(),
        ];
    }
}
