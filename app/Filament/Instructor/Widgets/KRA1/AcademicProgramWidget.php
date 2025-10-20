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

class AcademicProgramWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'program-development')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.program_name')->label('Program Name')->wrap(),
                Tables\Columns\TextColumn::make('data.program_type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('data.role')->label('Role')->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title()),
                Tables\Columns\TextColumn::make('data.academic_year')->label('Year Implemented'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'program-development';
                        return $data;
                    })
                    ->modalHeading('Submit Academic Program Development')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Academic Program Development')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('data.program_name')->label('Name of Academic Degree Program')->required()->columnSpanFull(),
            Select::make('data.program_type')
                ->label('Type of Program')
                ->options([
                    'newly-developed' => 'Newly Developed',
                    'revised' => 'Revised',
                ])
                ->required(),
            Select::make('data.role')
                ->label('Role in Program Development/Revision')
                ->options([
                    'lead' => 'Lead',
                    'contributor' => 'Contributor',
                ])
                ->required(),
            TextInput::make('data.board_approval_no')->label('Board Approval (Reso. No.)')->required(),
            TextInput::make('data.academic_year')->label('Academic Year Implemented')->placeholder('YYYY-YYYY')->maxLength(9)->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link)')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/program-development')
                ->columnSpanFull(),
        ];
    }
}
