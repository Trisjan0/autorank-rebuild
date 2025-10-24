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

class SocialResponsibilityWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'extension-social-responsibility')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Activity')->wrap(),
                Tables\Columns\TextColumn::make('data.community_name')->label('Name of Community'),
                Tables\Columns\TextColumn::make('data.role')->label('Role')->badge(),
                Tables\Columns\TextColumn::make('data.activity_date')->label('Activity Date')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA III';
                        $data['type'] = 'extension-social-responsibility';
                        return $data;
                    })
                    ->modalHeading('Submit New Social Responsibility Project')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Social Responsibility Project')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.name')
                ->label('Name of Community Extension Activity')
                ->required()
                ->columnSpanFull(),
            TextInput::make('data.community_name')
                ->label('Name of Community')
                ->required(),
            TextInput::make('data.beneficiary_count')
                ->label('No. of Beneficiaries')
                ->numeric()
                ->required(),
            TextInput::make('data.role')
                ->label('Role')
                ->required(),
            DatePicker::make('data.activity_date')
                ->label('Activity Date')
                ->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-social')
                ->columnSpanFull(),
        ];
    }
}
