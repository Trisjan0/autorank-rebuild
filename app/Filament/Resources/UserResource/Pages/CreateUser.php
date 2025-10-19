<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Automatically set rank assignment details on creation.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['faculty_rank_id'])) {
            $data['rank_assigned_by'] = Auth::user()->email;
            $data['rank_assigned_at'] = now();
        } else {
            $data['rank_assigned_by'] = null;
            $data['rank_assigned_at'] = null;
        }

        return $data;
    }

    /**
     * This hook runs after the user is created to correctly assign the role.
     */
    protected function afterCreate(): void
    {
        // Get the role ID from the form data.
        $roleId = $this->form->getState()['role_id'];

        if ($roleId) {
            // Find the role name from the ID.
            $roleName = Role::find($roleId)?->name;

            if ($roleName) {
                // Assign the role to the newly created user record.
                $this->record->assignRole($roleName);
            }
        }
    }
}
