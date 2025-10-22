<?php

namespace App\Filament\Instructor\Pages\KRA2;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CreativeWorksPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.instructor.pages.k-r-a2.creative-works-page';

    protected static ?string $navigationGroup = 'KRA II: Research, Innovation & Creative Works';

    protected static ?string $navigationLabel = 'C: Creative Works';

    protected static ?string $title = 'C: Creative Works';

    protected static ?int $navigationSort = 3;

    public ?string $activeTab = 'performing_art';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
