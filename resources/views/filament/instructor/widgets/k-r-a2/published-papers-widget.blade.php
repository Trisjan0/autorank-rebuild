<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'sole_authorship')"
                    :active="$activeTable === 'sole_authorship'"
                    icon="heroicon-o-user">
                    Sole Authorship
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'co_authorship')"
                    :active="$activeTable === 'co_authorship'"
                    icon="heroicon-o-users">
                    Co-Authorship
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>