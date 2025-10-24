<?php

namespace App\Filament\Instructor\Pages\KRA3;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ServiceToInstitutionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static string $view = 'filament.instructor.pages.k-r-a3.service-to-institution-page';

    protected static ?string $navigationGroup = 'KRA III: Extension';

    protected static ?string $navigationLabel = 'A: Service to Institution';

    protected static ?string $title = 'A: Service to Institution';

    protected static ?int $navigationSort = 1;

    public ?string $activeTab = 'linkages';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
