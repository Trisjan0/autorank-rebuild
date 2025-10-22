<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'patented_inventions')"
            :active="$activeTab === 'patented_inventions'"
            icon="heroicon-o-key">
            Patented Inventions
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'non_patentable_inventions')"
            :active="$activeTab === 'non_patentable_inventions'"
            icon="heroicon-o-cpu-chip">
            Non-Patentable Inventions
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'patented_inventions') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\PatentedInventionsWidget::class)
    </div>

    <div @if($activeTab !== 'non_patentable_inventions') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\NonPatentableInventionsWidget::class)
    </div>

</x-filament-panels::page>