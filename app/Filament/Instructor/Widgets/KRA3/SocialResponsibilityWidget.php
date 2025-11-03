<?php

namespace App\Filament\Instructor\Widgets\KRA3;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Forms\Components\TrimmedIntegerInput;
use App\Tables\Columns\ScoreColumn;

class SocialResponsibilityWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA III')
                    ->where('type', 'social_responsibility')
            )
            ->heading('Institutional Social Responsibility Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.activity_title')->label('Activity Title')->wrap(),
                Tables\Columns\TextColumn::make('data.community_name')->label('Community Name'),
                Tables\Columns\TextColumn::make('data.beneficiary_count')->label('Beneficiaries'),
                Tables\Columns\TextColumn::make('data.role')
                    ->label('Role')
                    ->formatStateUsing(fn(?string $state): string => Str::title($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('data.activity_date')->label('Activity Date')->date(),
                ScoreColumn::make('score'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()?->activeApplication?->id ?? null; // temporarily allow no application id submission
                        $data['category'] = 'KRA III';
                        $data['type'] = 'social_responsibility';
                        return $data;
                    })
                    ->modalHeading('Submit New Social Responsibility Activity')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Social Responsibility Activity')
                    ->modalWidth('3xl'),
                DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.activity_title')
                ->label('Name of Community Extension Activity')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),

            TextInput::make('data.community_name')
                ->label('Name of Community/Sponsoring Organization')
                ->required()
                ->maxLength(255),

            TrimmedIntegerInput::make('data.beneficiary_count')
                ->label('No. of Beneficiaries')
                ->required()
                ->minValue(1),

            Select::make('data.role')
                ->label('Role')
                ->options([
                    'head' => 'Head',
                    'participant' => 'Participant',
                ])
                ->searchable()
                ->required(),

            DatePicker::make('data.activity_date')
                ->label('Activity Date')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),

            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra3-social-resp')
                ->columnSpanFull(),
        ];
    }
}
