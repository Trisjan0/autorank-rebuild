<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'linkages')"
            :active="$activeTab === 'linkages'"
            icon="heroicon-o-link">
            Linkages & Networking
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'income_generation')"
            :active="$activeTab === 'income_generation'"
            icon="heroicon-o-currency-dollar">
            Income Generation
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'linkages') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA3\LinkagesWidget::class)
    </div>

    <div @if($activeTab !== 'income_generation') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA3\IncomeGenerationWidget::class)
    </div>

</x-filament-panels::page>