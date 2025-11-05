<x-filament-widgets::widget>
    <x-filament::card>

        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs class="mb-4">
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

        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>