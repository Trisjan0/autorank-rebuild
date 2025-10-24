<?php

namespace App\Filament\Instructor\Pages\KRA4;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BonusIndicatorsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static string $view = 'filament.instructor.pages.k-r-a4.bonus-indicators-page';

    protected static ?string $navigationGroup = 'KRA IV: Professional Development';

    protected static ?string $navigationLabel = 'D: Bonus Indicators';

    protected static ?string $title = 'D: Bonus Indicators for Newly Appointed Faculty';

    protected static ?int $navigationSort = 4;

    public ?string $activeTab = 'academic_service';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
