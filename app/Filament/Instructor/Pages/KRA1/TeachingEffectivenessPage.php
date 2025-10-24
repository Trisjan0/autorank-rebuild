<?php

namespace App\Filament\Instructor\Pages\KRA1;

use App\Filament\Instructor\Widgets\KRA1\TeachingEffectivenessWidget;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TeachingEffectivenessPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.instructor.pages.k-r-a1.teaching-effectiveness-page';

    protected static ?string $navigationGroup = 'KRA I: Instruction';

    protected static ?string $title = 'A: Teaching Effectiveness';

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
