<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'academic_service')"
            :active="$activeTab === 'academic_service'"
            icon="heroicon-o-building-library">
            Academic Service
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'industry_experience')"
            :active="$activeTab === 'industry_experience'"
            icon="heroicon-o-briefcase">
            Industry Experience
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'academic_service') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA4\AcademicServiceWidget::class)
    </div>

    <div @if($activeTab !== 'industry_experience') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA4\IndustryExperienceWidget::class)
    </div>

</x-filament-panels::page>