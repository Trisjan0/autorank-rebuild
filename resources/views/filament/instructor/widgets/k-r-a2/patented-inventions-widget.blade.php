<x-filament-widgets::widget>
    <x-filament::card>
        
        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs wire:model.live="activeTable" class="mb-4">
            <x-filament::tabs.item
                wire:click="$set('activeTable', 'invention_patent_sole')"
                :active="$activeTable === 'invention_patent_sole'">
                Invention (Sole)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="$set('activeTable', 'invention_patent_co')"
                :active="$activeTable === 'invention_patent_co'">
                Invention (Multiple)
            </x-filament::tabs.item>
            
            <x-filament::tabs.item
                wire:click="$set('activeTable', 'utility_design_sole')"
                :active="$activeTable === 'utility_design_sole'">
                Utility/Design (Sole)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="$set('activeTable', 'utility_design_co')"
                :active="$activeTable === 'utility_design_co'">
                Utility/Design (Multiple)
            </x-filament::tabs.item>
            
            <x-filament::tabs.item
                wire:click="$set('activeTable', 'commercialized_local')"
                :active="$activeTable === 'commercialized_local'">
                Commercialized (Local)
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="$set('activeTable', 'commercialized_intl')"
                :active="$activeTable === 'commercialized_intl'">
                Commercialized (International)
            </x-filament::tabs.item>
        </x-filament::tabs>
        
        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>