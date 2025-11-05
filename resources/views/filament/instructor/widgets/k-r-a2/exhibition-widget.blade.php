<x-filament-widgets::widget>
    <x-filament::card>

        @include('filament.instructor.widgets.partials.kra-widget-header')

        {{ $this->table }}
        
    </x-filament::card>
</x-filament-widgets::widget>