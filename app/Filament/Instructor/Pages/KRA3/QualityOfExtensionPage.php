<?php

namespace App\Filament\Instructor\Pages\KRA3;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class QualityOfExtensionPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static string $view = 'filament.instructor.pages.k-r-a3.quality-of-extension-page';

    protected static ?string $navigationGroup = 'KRA III: Extension';

    protected static ?string $navigationLabel = 'C: Quality of Extension';

    protected static ?string $title = 'C: Quality of Extension Services';

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
