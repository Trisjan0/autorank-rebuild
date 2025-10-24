<?php

namespace App\Filament\Instructor\Pages\KRA3;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BonusCriterionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static string $view = 'filament.instructor.pages.k-r-a3.bonus-criterion-page';

    protected static ?string $navigationGroup = 'KRA III: Extension';

    protected static ?string $navigationLabel = 'D: Bonus Criterion';

    protected static ?string $title = 'D: Bonus Criterion';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
