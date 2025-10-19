<?php

namespace App\Filament\Traits;

use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Cache;
use Spatie\Valuestore\Valuestore;

trait ManagesPanelColors
{
    /**
     * Provides the default colors as consistent hex codes.
     */
    private static function getBaseDefaultColors(): array
    {
        return [
            'primary'   => '#0288EC',
            'secondary' => '#6b7280',
            'danger'    => '#ef4444',
            'success'   => '#22c55e',
            'warning'   => '#eab308',
        ];
    }

    /**
     * Get the default colors for the settings page.
     */
    public static function getDefaultColors(): array
    {
        return self::getBaseDefaultColors();
    }

    /**
     * Get the centrally managed theme colors for a Filament panel.
     */
    public function getPanelColors(): array
    {
        $settings = Cache::rememberForever('app.settings', function () {
            if (! file_exists(config('settings.path'))) {
                return null;
            }
            return Valuestore::make(config('settings.path'));
        });

        $colors = self::getBaseDefaultColors();

        // Override defaults with any colors saved in the settings file.
        if ($settings) {
            foreach ($colors as $key => $value) {
                $colors[$key] = $settings->get($key, $value);
            }
        }

        // Process all values to ensure they are full color arrays for Filament.
        foreach ($colors as $key => $value) {
            if (is_string($value)) {
                $colors[$key] = Color::hex($value);
            }
        }

        return $colors;
    }
}
