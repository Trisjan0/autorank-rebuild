<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <x-filament::tabs wire:model.live="activeTable">
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'software_new_sole')"
                    :active="$activeTable === 'software_new_sole'">
                    New Software (Sole)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'software_new_co')"
                    :active="$activeTable === 'software_new_co'">
                    New Software (Multiple)
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'software_updated')"
                    :active="$activeTable === 'software_updated'">
                    Updated Software (Sole/Co-Developer)
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'plant_animal_sole')"
                    :active="$activeTable === 'plant_animal_sole'">
                    Plant/Animal/Microbe (Sole)
                </x-filament::tabs.item>
                
                <x-filament::tabs.item
                    wire:click="$set('activeTable', 'plant_animal_co')"
                    :active="$activeTable === 'plant_animal_co'">
                    Plant/Animal/Microbe (Multiple)
                </x-filament::tabs.item>
            </x-filament::tabs>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>