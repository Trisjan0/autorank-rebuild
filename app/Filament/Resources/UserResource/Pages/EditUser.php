<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Automatically update rank assignment details on change ---
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Get the original faculty rank ID from the record being edited.
        $originalRankId = $this->getRecord()->faculty_rank_id;

        // Check if the faculty rank in the form data is different from the original.
        if ($originalRankId != $data['faculty_rank_id']) {
            // If a rank was added or changed...
            if (filled($data['faculty_rank_id'])) {
                $data['rank_assigned_by'] = Auth::user()->email;
                $data['rank_assigned_at'] = now();
            } else {
                // If the rank was cleared (set to 'Unset')...
                $data['rank_assigned_by'] = null;
                $data['rank_assigned_at'] = null;
            }
        }

        return $data;
    }
}
