<?php

namespace App\Filament\Instructor\Pages\KRA2;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class InventionsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static string $view = 'filament.instructor.pages.k-r-a2.inventions-page';

    protected static ?string $navigationGroup = 'KRA II: Research, Innovation & Creative Works';

    protected static ?string $navigationLabel = 'B: Inventions';

    protected static ?string $title = 'B: Inventions';

    protected static ?int $navigationSort = 2;

    public ?string $activeTab = 'patented_inventions';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
