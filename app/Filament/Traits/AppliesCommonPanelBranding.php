<?php

namespace App\Filament\Traits;

use Filament\Panel;

trait AppliesCommonPanelBranding
{
    public function applySharedBranding(Panel $panel): Panel
    {
        return $panel
            ->brandLogo(fn() => view('filament.shared.logo'))
            ->renderHook(
                'panels::user-menu.before',
                fn() => view('filament.shared.user-role'),
            );
    }
}
