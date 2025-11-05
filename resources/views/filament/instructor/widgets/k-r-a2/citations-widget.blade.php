<x-filament-widgets::widget>
    <x-filament::card>
        
        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs wire:model.live="activeTable" class="mb-4">
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
        
        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>