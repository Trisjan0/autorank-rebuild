<?php

namespace App\Filament\Resources\FacultyRankResource\Pages;

use App\Filament\Resources\FacultyRankResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacultyRanks extends ListRecords
{
    protected static string $resource = FacultyRankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
