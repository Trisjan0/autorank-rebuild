<x-filament-widgets::widget>
    <x-filament::card>
        
        @include('filament.instructor.widgets.partials.kra-widget-header')

        <x-filament::tabs class="mb-4">
            <x-filament::tabs.item
                :active="$activeTable === 'student_evaluation'"
                wire:click="$set('activeTable', 'student_evaluation')"
                icon="heroicon-o-users"
            >
                Student Evaluation
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeTable === 'supervisor_evaluation'"
                wire:click="$set('activeTable', 'supervisor_evaluation')"
                icon="heroicon-o-user"
            >
                Supervisor Evaluation
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{ $this->table }}
    </x-filament::card>
</x-filament-widgets::widget>