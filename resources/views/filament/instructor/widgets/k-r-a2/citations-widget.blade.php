<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'local_authors')"
                    :active="$activeTable === 'local_authors'"
                    icon="heroicon-o-chat-bubble-left-right">
                    Local Authors
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'international_authors')"
                    :active="$activeTable === 'international_authors'"
                    icon="heroicon-o-globe-alt">
                    International Authors
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>