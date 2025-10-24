<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'doctorate_degree')"
                    :active="$activeTable === 'doctorate_degree'"
                    icon="heroicon-o-academic-cap">
                    Doctorate Degree (First Time)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'additional_degrees')"
                    :active="$activeTable === 'additional_degrees'"
                    icon="heroicon-o-plus-circle">
                    Additional Degrees
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>