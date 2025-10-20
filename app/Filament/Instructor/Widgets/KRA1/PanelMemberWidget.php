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

class PanelMemberWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'mentorship-panel')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.requirement_type')
                    ->label('Requirement')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge(),
                Tables\Columns\TextColumn::make('data.times_served')->label('No. of Times Served (All AYs)'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'mentorship-panel';
                        return $data;
                    })
                    ->modalHeading('Submit Services as Panel Member'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->form($this->getFormSchema()),
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
            TextInput::make('data.times_served')
                ->label('No. of Times Served (All AYs)')
                ->numeric()
                ->required()
                ->default(0),
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
