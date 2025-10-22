<?php

namespace App\Filament\Instructor\Pages\KRA2;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ResearchOutputsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $view = 'filament.instructor.pages.k-r-a2.research-outputs-page';

    protected static ?string $navigationGroup = 'KRA II: Research, Innovation & Creative Works';

    protected static ?string $navigationLabel = 'A: Research Outputs';

    protected static ?string $title = 'A: Research Outputs';

    protected static ?int $navigationSort = 1;

    public ?string $activeTab = 'published_papers';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
