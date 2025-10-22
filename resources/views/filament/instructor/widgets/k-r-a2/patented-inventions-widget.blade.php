<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'invention_sole')"
                    :active="$activeTable === 'invention_sole'">
                    Invention (Sole)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'invention_multiple')"
                    :active="$activeTable === 'invention_multiple'">
                    Invention (Multiple)
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'utility_design_sole')"
                    :active="$activeTable === 'utility_design_sole'">
                    Utility/Design (Sole)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'utility_design_multiple')"
                    :active="$activeTable === 'utility_design_multiple'">
                    Utility/Design (Multiple)
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'commercialized_local')"
                    :active="$activeTable === 'commercialized_local'">
                    Commercialized (Local)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'commercialized_international')"
                    :active="$activeTable === 'commercialized_international'">
                    Commercialized (Int'l)
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>