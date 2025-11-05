<x-filament-widgets::widget>
    <x-filament::card>

        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs wire:model.live="activeTable" class="mb-4">
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

        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>