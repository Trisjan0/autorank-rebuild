<?php

namespace App\Filament\Instructor\Pages\KRA4;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfessionalOrganizationsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static string $view = 'filament.instructor.pages.k-r-a4.professional-organizations-page';

    protected static ?string $navigationGroup = 'KRA IV: Professional Development';

    protected static ?string $navigationLabel = 'A: Professional Organizations';

    protected static ?string $title = 'A: Involvement in Professional Organizations';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
