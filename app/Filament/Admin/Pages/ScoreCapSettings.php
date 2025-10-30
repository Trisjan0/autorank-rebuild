<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ScoreCapSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Score Caps';
    protected static ?string $title = 'Score Cap Settings';

    protected static string $view = 'filament.admin.pages.score-cap-settings';

    public ?array $data = [];

    /**
     * Define the array of all setting keys we will manage.
     */
    protected function getSettingKeys(): array
    {
        return [
            // KRA I
            'kra_1_total_cap',
            'kra_1_a_cap',
            'kra_1_b_cap',
            'kra_1_c_cap',
            // KRA II
            'kra_2_total_cap',
            'kra_2_a_cap',
            'kra_2_a_3_1_cap',
            'kra_2_a_3_2_cap',
            'kra_2_b_cap',
            'kra_2_b_1_2_1_cap',
            'kra_2_b_1_2_2_cap',
            'kra_2_c_cap',
            // KRA III
            'kra_3_total_cap',
            'kra_3_a_cap',
            'kra_3_b_cap',
            'kra_3_c_cap',
            'kra_3_d_cap',
            // KRA IV
            'kra_4_total_cap',
            'kra_4_a_cap',
            'kra_4_b_cap',
            'kra_4_b_2_cap',
            'kra_4_b_3_cap',
            'kra_4_c_cap',
            'kra_4_d_cap',
        ];
    }

    /**
     * Define the array of default cap values.
     */
    protected function getDefaultCaps(): array
    {
        return [
            // KRA I
            ['key' => 'kra_1_total_cap', 'value' => '100'],
            ['key' => 'kra_1_a_cap', 'value' => '60'],
            ['key' => 'kra_1_b_cap', 'value' => '30'],
            ['key' => 'kra_1_c_cap', 'value' => '10'],
            // KRA II
            ['key' => 'kra_2_total_cap', 'value' => '100'],
            ['key' => 'kra_2_a_cap', 'value' => '100'],
            ['key' => 'kra_2_a_3_1_cap', 'value' => '40'],
            ['key' => 'kra_2_a_3_2_cap', 'value' => '60'],
            ['key' => 'kra_2_b_cap', 'value' => '100'],
            ['key' => 'kra_2_b_1_2_1_cap', 'value' => '20'],
            ['key' => 'kra_2_b_1_2_2_cap', 'value' => '30'],
            ['key' => 'kra_2_c_cap', 'value' => '100'],
            // KRA III
            ['key' => 'kra_3_total_cap', 'value' => '100'],
            ['key' => 'kra_3_a_cap', 'value' => '30'],
            ['key' => 'kra_3_b_cap', 'value' => '50'],
            ['key' => 'kra_3_c_cap', 'value' => '20'],
            ['key' => 'kra_3_d_cap', 'value' => '20'],
            // KRA IV
            ['key' => 'kra_4_total_cap', 'value' => '100'],
            ['key' => 'kra_4_a_cap', 'value' => '20'],
            ['key' => 'kra_4_b_cap', 'value' => '60'],
            ['key' => 'kra_4_b_2_cap', 'value' => '10'],
            ['key' => 'kra_4_b_3_cap', 'value' => '10'],
            ['key' => 'kra_4_c_cap', 'value' => '20'],
            ['key' => 'kra_4_d_cap', 'value' => '20'],
        ];
    }

    /**
     * Pre-fill the form with values from the database.
     */
    public function mount(): void
    {
        $keys = $this->getSettingKeys();

        $settings = Setting::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->all();

        $defaults = collect($this->getDefaultCaps())->pluck('value', 'key');

        $formData = [];
        foreach ($keys as $key) {
            $formData[$key] = $settings[$key] ?? $defaults[$key] ?? 0;
        }

        $this->form->fill($formData);
    }

    /**
     * Define the form structure.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('KRA I: Instruction')
                    ->description('Set score caps for Instruction.')
                    ->schema([
                        TextInput::make('kra_1_total_cap')->label('Total KRA I Cap')
                            ->integer()->required()->minValue(0)->maxValue(500),
                        Grid::make(4)->schema([
                            TextInput::make('kra_1_a_cap')->label('A: Teaching Effectiveness Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_1_b_cap')->label('B: Materials Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_1_c_cap')->label('C: Mentorship Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                        ]),
                    ]),

                Section::make('KRA II: Research, Innovation & Creative Work')
                    ->description('Set score caps for Research.')
                    ->schema([
                        TextInput::make('kra_2_total_cap')->label('Total KRA II Cap')
                            ->integer()->required()->minValue(0)->maxValue(500),
                        Grid::make(4)->schema([
                            TextInput::make('kra_2_a_cap')->label('A: Research Outputs Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_a_3_1_cap')->label('A (Sub): Local Citations')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_a_3_2_cap')->label('A (Sub): International Citations')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_b_cap')->label('B: Inventions Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_b_1_2_1_cap')->label('B (Sub): Local Patented')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_b_1_2_2_cap')->label('B (Sub): International Patented')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_2_c_cap')->label('C: Creative Works Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                        ]),
                    ]),

                Section::make('KRA III: Extension')
                    ->description('Set score caps for Extension.')
                    ->schema([
                        TextInput::make('kra_3_total_cap')->label('Total KRA III Cap')
                            ->integer()->required()->minValue(0)->maxValue(500),
                        Grid::make(4)->schema([
                            TextInput::make('kra_3_a_cap')->label('A: Service to Institution')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_3_b_cap')->label('B: Service to Community')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_3_c_cap')->label('C: Quality of Extension')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_3_d_cap')->label('D: Bonus Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                        ]),
                    ]),

                Section::make('KRA IV: Professional Development')
                    ->description('Set score caps for Professional Development.')
                    ->schema([
                        TextInput::make('kra_4_total_cap')->label('Total KRA IV Cap')
                            ->integer()->required()->minValue(0)->maxValue(500),
                        Grid::make(4)->schema([
                            TextInput::make('kra_4_a_cap')->label('A: Prof. Organizations')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_4_b_cap')->label('B: Continuing Development')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_4_b_2_cap')->label('B (Sub): Participation')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_4_b_3_cap')->label('B (Sub): Paper Presentation')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_4_c_cap')->label('C: Awards Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                            TextInput::make('kra_4_d_cap')->label('D: Bonus Cap')
                                ->integer()->required()->minValue(0)->maxValue(500),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Save the form data to the database.
     */
    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Cache::forget('app.settings');

        Notification::make()
            ->title('Score caps saved successfully')
            ->success()
            ->send();
    }

    /**
     * Reset all caps to their default (seeder) values.
     */
    public function resetToDefaults(): void
    {
        $caps = $this->getDefaultCaps();

        foreach ($caps as $cap) {
            Setting::updateOrCreate(
                ['key' => $cap['key']],
                ['value' => $cap['value']]
            );
        }

        // Re-run mount to fill the form with the new default values
        $this->mount();

        Cache::forget('app.settings');

        Notification::make()
            ->title('Score caps have been reset to default')
            ->success()
            ->send();
    }
}
