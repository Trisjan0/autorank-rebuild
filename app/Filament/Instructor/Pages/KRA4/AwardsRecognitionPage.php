<?php

namespace App\Filament\Instructor\Pages\KRA4;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AwardsRecognitionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static string $view = 'filament.instructor.pages.k-r-a4.awards-recognition-page';

    protected static ?string $navigationGroup = 'KRA IV: Professional Development';

    protected static ?string $navigationLabel = 'C: Awards & Recognition';

    protected static ?string $title = 'C: Awards and Recognition';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
