<?php

namespace App\Filament\Instructor\Widgets\KRA4;

use App\Models\Submission;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class ProfessionalOrganizationsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'profdev-organization')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.name')->label('Name of Organization')->wrap(),
                Tables\Columns\TextColumn::make('data.type')->label('Type of Organization')->badge(),
                Tables\Columns\TextColumn::make('data.role')->label('Role/Contribution'),
                Tables\Columns\TextColumn::make('data.date_activity')->label('Date of Activity')->date(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Involvement')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
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
                ->columnSpanFull(),
            TextInput::make('data.type')
                ->label('Type of Organization')
                ->required(),
            DatePicker::make('data.date_activity')
                ->label('Date of Activity')
                ->required(),
            Textarea::make('data.activity')
                ->label('Activity of the Organization Participated by the Faculty')
                ->required()
                ->columnSpanFull(),
            Textarea::make('data.role')
                ->label('Role or Contribution to the Activity of the Organization')
                ->required()
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
