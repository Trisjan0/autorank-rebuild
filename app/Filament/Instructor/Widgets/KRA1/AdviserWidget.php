<?php

namespace App\Filament\Instructor\Widgets\KRA1;

use App\Models\Submission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdviserWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'mentorship-adviser')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.requirement_type')
                    ->label('Requirement')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.ay_2019_2020')->label('AY 2019-2020'),
                Tables\Columns\TextColumn::make('data.ay_2020_2021')->label('AY 2020-2021'),
                Tables\Columns\TextColumn::make('data.ay_2021_2022')->label('AY 2021-2022'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'mentorship-adviser';
                        return $data;
                    })
                    ->modalHeading('Submit Services as Adviser')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form($this->getFormSchema())->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('data.requirement_type')
                ->label('Requirement')
                ->options([
                    'special_project' => 'Special/Capstone Project',
                    'undergraduate_thesis' => 'Undergraduate Thesis',
                    'masters_thesis' => 'Master\'s Thesis',
                    'dissertation' => 'Dissertation',
                ])
                ->required(),
            TextInput::make('data.ay_2019_2020')->label('No. of Advisees (AY 2019-2020)')->numeric()->required()->default(0),
            TextInput::make('data.ay_2020_2021')->label('No. of Advisees (AY 2020-2021)')->numeric()->required()->default(0),
            TextInput::make('data.ay_2021_2022')->label('No. of Advisees (AY 2021-2022)')->numeric()->required()->default(0),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s)')
                ->helperText('Upload consolidated proof for this requirement type.')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/mentorship')
                ->columnSpanFull(),
        ];
    }
}
