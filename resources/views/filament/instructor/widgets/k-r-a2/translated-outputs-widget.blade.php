<x-filament-widgets::widget>
    <x-filament::card>

        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs wire:model.live="activeTable" class="mb-4">
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

        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>