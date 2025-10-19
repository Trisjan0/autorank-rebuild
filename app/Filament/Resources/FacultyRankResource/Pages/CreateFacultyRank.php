<?php

namespace App\Filament\Resources\FacultyRankResource\Pages;

use App\Filament\Resources\FacultyRankResource;
use App\Models\FacultyRank;
use Filament\Resources\Pages\CreateRecord;

class CreateFacultyRank extends CreateRecord
{
    protected static string $resource = FacultyRankResource::class;

    /**
     * This method is a Filament lifecycle hook that runs immediately
     * after the new FacultyRank record has been created in the database.
     */
    protected function afterCreate(): void
    {
        // Get the level of the rank that has just been created.
        $newLevel = $this->record->level;

        // Find all other ranks that are at or above the new level.
        // The where('id', '!=', ...) clause ensures we don't increment the
        // level of the record that has just been created.
        // The increment('level') command efficiently updates them all with a single query.
        FacultyRank::where('level', '>=', $newLevel)
            ->where('id', '!=', $this->record->id)
            ->increment('level');
    }
}
