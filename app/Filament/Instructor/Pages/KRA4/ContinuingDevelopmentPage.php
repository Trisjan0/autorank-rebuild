<?php

namespace App\Filament\Instructor\Pages\KRA4;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ContinuingDevelopmentPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.instructor.pages.k-r-a4.continuing-development-page';

    protected static ?string $navigationGroup = 'KRA IV: Professional Development';

    protected static ?string $navigationLabel = 'B: Continuing Development';

    protected static ?string $title = 'B: Continuing Development';

    protected static ?int $navigationSort = 2;

    public ?string $activeTab = 'educational_qualifications';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
