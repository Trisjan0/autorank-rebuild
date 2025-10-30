<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Traits\ManagesPanelColors;
use App\Models\User;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Valuestore\Valuestore;

class ThemeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Theme Settings';
    protected static ?string $title = 'Theme Settings';

    protected static string $view = 'filament.admin.pages.theme-settings';

    public ?array $data = [];

    /**
     * This method runs when the component is first loaded.
     * It pre-populates the form with saved settings and falls back
     * to the centralized defaults.
     */
    public function mount(): void
    {
        $settings = file_exists(config('settings.path'))
            ? Valuestore::make(config('settings.path'))->all()
            : [];

        // Fetch the defaults from the centralized trait.
        // This ensures the color pickers show the default colors on first load.
        $defaultData = ManagesPanelColors::getDefaultColors();

        // Fill the form, letting saved settings override the defaults.
        $this->form->fill(array_merge($defaultData, $settings));
    }

    /**
     * Define the structure of the settings form.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Website Theme')
                    ->description('Customize the main colors of the website.')
                    ->schema([
                        ColorPicker::make('primary')
                            ->label('Primary Color')
                            ->hexColor(),

                        ColorPicker::make('secondary')
                            ->label('Secondary Color')
                            ->hexColor(),

                        ColorPicker::make('danger')
                            ->label('Danger Color')
                            ->hexColor(),

                        ColorPicker::make('success')
                            ->label('Success Color')
                            ->hexColor(),

                        ColorPicker::make('warning')
                            ->label('Warning Color')
                            ->hexColor(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    /**
     * Save the form data to the settings file.
     */
    public function save(): void
    {
        $data = $this->form->getState();
        $settings = Valuestore::make(config('settings.path'));

        foreach ($data as $key => $value) {
            if ($value) {
                $settings->put($key, $value);
            } else {
                $settings->forget($key);
            }
        }

        $this->updateCacheAndNotify('Website theme settings saved successfully');
    }

    /**
     * Reset all colors to their default values by removing them from the settings file.
     */
    public function resetColors(): void
    {
        $settings = Valuestore::make(config('settings.path'));
        $colorKeys = ['primary', 'secondary', 'danger', 'success', 'warning'];

        // Loop and remove each key.
        foreach ($colorKeys as $key) {
            $settings->forget($key);
        }

        // Fill the form with the defaults for immediate visual feedback.
        $this->form->fill(ManagesPanelColors::getDefaultColors());

        $this->updateCacheAndNotify('Website theme colors reset to default', 'success');
    }

    /**
     * A helper function to clear the cache, send a notification, and reload the page.
     */
    private function updateCacheAndNotify(string $message, string $status = 'success'): void
    {
        Cache::forget('app.settings');

        Notification::make()
            ->title($message)
            ->{$status}()
            ->send();

        // Reload the page to apply the new theme colors instantly.
        $this->js('window.location.reload()');
    }

    /**
     * Control who can access this settings page.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole(['Admin', 'Super Admin']);
    }
}
