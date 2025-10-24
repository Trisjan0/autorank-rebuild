<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'student_evaluation')"
                    :active="$activeTable === 'student_evaluation'"
                    icon="heroicon-o-academic-cap">
                    Student Evaluation
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'supervisor_evaluation')"
                    :active="$activeTable === 'supervisor_evaluation'"
                    icon="heroicon-o-clipboard-document-check">
                    Supervisor Evaluation
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>