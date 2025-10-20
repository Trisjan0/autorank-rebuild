<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'sole_authorship')"
            :active="$activeTab === 'sole_authorship'"
            icon="heroicon-o-user">
            Sole Authorship
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'co_authorship')"
            :active="$activeTab === 'co_authorship'"
            icon="heroicon-o-users">
            Co-Authorship
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'program_development')"
            :active="$activeTab === 'program_development'"
            icon="heroicon-o-building-library">
            Academic Program Development
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'sole_authorship') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\InstructionalMaterialsWidget::class)
    </div>

    <div @if($activeTab !== 'co_authorship') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\CoAuthorshipWidget::class)
    </div>

    <div @if($activeTab !== 'program_development') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\AcademicProgramWidget::class)
    </div>

</x-filament-panels::page>