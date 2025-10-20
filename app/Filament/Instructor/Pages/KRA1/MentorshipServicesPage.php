<?php

namespace App\Filament\Instructor\Pages\KRA1;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MentorshipServicesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.instructor.pages.k-r-a1.mentorship-services-page';

    protected static ?string $navigationGroup = 'KRA I: Instruction';

    protected static ?string $title = 'C: Mentorship Services';

    protected static ?int $navigationSort = 3;

    public ?string $activeTab = 'adviser';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
