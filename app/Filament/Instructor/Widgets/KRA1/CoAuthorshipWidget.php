<?php

namespace App\Filament\Instructor\Widgets\KRA1;

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

class CoAuthorshipWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Submission::query()
                    ->where('user_id', Auth::id())
                    ->where('type', 'im-co-authorship')
            )
            ->heading('Submissions')
            ->columns([
                Tables\Columns\TextColumn::make('data.title')->label('Title')->wrap(),
                Tables\Columns\TextColumn::make('data.contribution_percentage')->label('% Contribution')->suffix('%'),
                Tables\Columns\TextColumn::make('data.material_type')->label('Material Type')->formatStateUsing(fn(?string $state): string => Str::of($state)->replace('_', ' ')->title())->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        $data['application_id'] = Auth::user()->activeApplication->id;
                        $data['category'] = 'KRA I';
                        $data['type'] = 'im-co-authorship';
                        return $data;
                    })
                    ->modalHeading('Submit New Instructional Material (Co-Authorship)')
                    ->modalWidth('3xl'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getFormSchema())
                    ->modalHeading('Edit Instructional Material (Co-Authorship)')
                    ->modalWidth('3xl'),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('data.title')->label('Title of Instructional Material')->required()->columnSpanFull(),
            Select::make('data.material_type')
                ->label('Type of Material')
                ->options([
                    'textbook' => 'Textbook',
                    'textbook_chapter' => 'Textbook Chapter',
                    'manual_module' => 'Manual/Module/Workbook',
                    'multimedia_material' => 'Multimedia Teaching Material',
                    'testing_material' => 'Testing Material',
                ])
                ->required(),
            TextInput::make('data.reviewer')->label('Reviewer or Its Equivalent')->required(),
            TextInput::make('data.publisher')->label('Publisher/Repository')->required(),
            TextInput::make('data.contribution_percentage')
                ->label('% Contribution')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(99)
                ->suffix('%'),
            DatePicker::make('data.date_published')->label('Date Published')->required(),
            DatePicker::make('data.date_approved')->label('Date Approved for Use')->required(),
            FileUpload::make('google_drive_file_id')
                ->label('Proof Document(s) (Evidence Link & FORM 1-B1a)')
                ->helperText('Include the IM proof and the signed FORM 1-B1a for co-authorship.')
                ->multiple()
                ->reorderable()
                ->required()
                ->disk('private')
                ->directory('proof-documents/instructional-materials')
                ->columnSpanFull(),
        ];
    }
}
