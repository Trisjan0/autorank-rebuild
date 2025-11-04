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
                'panels::global-search.after',
                fn() => view('filament.shared.user-role'),
            );
    }
}
