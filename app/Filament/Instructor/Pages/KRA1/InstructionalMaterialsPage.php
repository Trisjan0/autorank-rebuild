<?php

namespace App\Filament\Instructor\Pages\KRA1;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class InstructionalMaterialsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.instructor.pages.k-r-a1.instructional-materials-page';

    protected static ?string $navigationGroup = 'KRA I: Instruction';

    protected static ?string $title = 'B: Instructional Materials';

    protected static ?int $navigationSort = 2;

    public ?string $activeTab = 'sole_authorship';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }
        return $user->hasRole(['Instructor']);
    }
}
