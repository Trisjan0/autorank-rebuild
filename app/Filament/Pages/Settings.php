<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Section;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            Setting::all()->pluck('value', 'key')->toArray()
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Website Theme')
                    ->description('Customize the main colors of the website.')
                    ->schema([
                        ColorPicker::make('primary')
                            ->label('Primary Color')
                            ->extraAttributes(['class' => 'h-10 w-full rounded-lg border border-gray-600']),

                        ColorPicker::make('secondary')
                            ->label('Secondary Color')
                            ->extraAttributes(['class' => 'h-10 w-full rounded-lg border border-gray-600']),

                        ColorPicker::make('danger')
                            ->label('Danger Color')
                            ->extraAttributes(['class' => 'h-10 w-full rounded-lg border border-gray-600']),

                        ColorPicker::make('success')
                            ->label('Success Color')
                            ->extraAttributes(['class' => 'h-10 w-full rounded-lg border border-gray-600']),

                        ColorPicker::make('warning')
                            ->label('Warning Color')
                            ->extraAttributes(['class' => 'h-10 w-full rounded-lg border border-gray-600']),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value)) {
                    Notification::make()
                        ->title("Invalid color format for '{$key}'. Must be a valid HEX color (e.g. #FF00FF).")
                        ->danger()
                        ->send();
                    return;
                }
            }
        }

        foreach ($data as $key => $value) {
            if ($value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            } else {
                Setting::where('key', $key)->delete();
            }
        }

        Cache::forget('settings');

        Notification::make()
            ->title('Color Settings saved successfully')
            ->success()
            ->send();

        $this->js('window.location.reload()');
    }

    public function resetColors(): void
    {
        $colorKeys = ['primary', 'secondary', 'danger', 'success', 'warning'];

        Setting::whereIn('key', $colorKeys)->delete();
        $this->form->fill([]);
        Cache::forget('settings');

        Notification::make()
            ->title('Colors reset to default')
            ->success()
            ->send();

        $this->js('window.location.reload()');
    }
}
