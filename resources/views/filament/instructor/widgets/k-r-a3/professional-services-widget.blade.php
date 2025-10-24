<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'accreditation_services')"
                    :active="$activeTable === 'accreditation_services'">
                    Accreditation/QA
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'judge_examiner')"
                    :active="$activeTable === 'judge_examiner'">
                    Judge/Examiner
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'consultant')"
                    :active="$activeTable === 'consultant'">
                    Consultant/Expert
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'media_service')"
                    :active="$activeTable === 'media_service'">
                    Media Service
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'training_resource_person')"
                    :active="$activeTable === 'training_resource_person'">
                    Training/Resource Person
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>