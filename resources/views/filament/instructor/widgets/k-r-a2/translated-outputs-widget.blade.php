<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'lead_researcher')"
                    :active="$activeTable === 'lead_researcher'"
                    icon="heroicon-o-user-plus">
                    Lead Researcher
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'contributor')"
                    :active="$activeTable === 'contributor'"
                    icon="heroicon-o-user-group">
                    Contributor
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>