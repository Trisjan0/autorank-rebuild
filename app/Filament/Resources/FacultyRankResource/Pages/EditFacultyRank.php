<?php

namespace App\Filament\Resources\FacultyRankResource\Pages;

use App\Filament\Resources\FacultyRankResource;
use App\Models\FacultyRank;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacultyRank extends EditRecord
{
    protected static string $resource = FacultyRankResource::class;

    /**
     * This hook runs before the form data is saved to the database.
     * It allows the manipulation of other records based on the incoming change.
     */
    protected function beforeSave(): void
    {
        // Get the original level before any changes were made.
        $originalLevel = $this->record->level;
        // Get the new level from the form data.
        $newLevel = $this->data['level'];

        // Only run the logic if the level has actually been changed.
        if ($originalLevel == $newLevel) {
            return;
        }

        // Case 1: The rank is moved to a HIGHER level number (e.g., from 3 to 5).
        // This means we need to shift ranks 4 and 5 UP to fill the gap.
        if ($newLevel > $originalLevel) {
            FacultyRank::whereBetween('level', [$originalLevel + 1, $newLevel])
                ->decrement('level');
        }

        // Case 2: The rank is moved to a LOWER level number (e.g., from 7 to 4).
        // This means we need to shift ranks 4, 5, and 6 DOWN to make room.
        if ($newLevel < $originalLevel) {
            FacultyRank::whereBetween('level', [$newLevel, $originalLevel - 1])
                ->increment('level');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
