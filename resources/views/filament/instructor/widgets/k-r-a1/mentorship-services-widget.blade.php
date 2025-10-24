<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'adviser')"
                    :active="$activeTable === 'adviser'"
                    icon="heroicon-o-academic-cap">
                    As Adviser
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'panel')"
                    :active="$activeTable === 'panel'"
                    icon="heroicon-o-users">
                    As Panel Member
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'mentor')"
                    :active="$activeTable === 'mentor'"
                    icon="heroicon-o-light-bulb">
                    Mentorship (Competitions)
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>