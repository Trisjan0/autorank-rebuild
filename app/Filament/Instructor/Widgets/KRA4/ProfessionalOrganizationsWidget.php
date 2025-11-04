<?php

namespace App\Filament\Instructor\Widgets\KRA4;

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
use App\Tables\Columns\ScoreColumn;

class ProfessionalOrganizationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('category', 'KRA IV')
                    ->where('type', 'profdev-organization')
                    ->where('application_id', Auth::user()?->activeApplication?->id ?? null)
            )
            ->heading('Involvement in Professional Organizations')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Organization')->wrap(),
                Tables\Columns\TextColumn::make('data.type')->label('Type of Organization')->badge(),
                Tables\Columns\TextColumn::make('data.role')
                    ->label('Role/Contribution')
                    ->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())
                    ->badge()
                    ->wrap(),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Date of Activity')->date(),
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
                        $data['type'] = 'profdev-organization';
                        return $data;
                    })
                    ->modalHeading('Submit New Involvement in Professional Organization')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Involvement in Professional Organization')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.name')
                ->label('Name of Organization')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('data.type')
                ->label('Type of Organization (e.g., Education)')
                ->required()
                ->maxLength(255),
            DatePicker::make('data.date_activity')
                ->label('Date of Activity')
                ->native(false)
                ->displayFormat('m/d/Y')
                ->required()
                ->maxDate(now()),
            Textarea::make('data.activity')
                ->label('Activity of the Organization Participated by the Faculty')
                ->required()
                ->maxLength(65535)
                ->columnSpanFull(),
            Select::make('data.role')
                ->label('Role or Contribution to the Activity')
                ->options([
                    'board_member' => 'Board Member',
                    'officer' => 'Officer',
                    'lead_organizer' => 'Lead Organizer',
                    'co_organizer' => 'Co-organizer',
                    'committee_chair' => 'Committee Chair',
                    'committee_member' => 'Committee Member',
                    'moderator' => 'Moderator',
                ])
                ->required()
                ->searchable()
                ->columnSpanFull(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/kra4-orgs')
                ->columnSpanFull(),
        ];
    }
}
