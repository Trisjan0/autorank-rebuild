<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'professional_services')"
            :active="$activeTab === 'professional_services'"
            icon="heroicon-o-briefcase">
            Professional Services
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'social_responsibility')"
            :active="$activeTab === 'social_responsibility'"
            icon="heroicon-o-globe-americas">
            Social Responsibility
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'professional_services') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA3\ProfessionalServicesWidget::class)
    </div>

    <div @if($activeTab !== 'social_responsibility') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA3\SocialResponsibilityWidget::class)
    </div>

</x-filament-panels::page>