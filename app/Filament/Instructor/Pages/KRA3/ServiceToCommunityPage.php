<?php

namespace App\Filament\Instructor\Pages\KRA3;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ServiceToCommunityPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.instructor.pages.k-r-a3.service-to-community-page';

    protected static ?string $navigationGroup = 'KRA III: Extension';

    protected static ?string $navigationLabel = 'B: Service to Community';

    protected static ?string $title = 'B: Service to Community';

    protected static ?int $navigationSort = 2;

    public ?string $activeTab = 'professional_services';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
