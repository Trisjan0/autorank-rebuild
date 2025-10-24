<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'educational_qualifications')"
            :active="$activeTab === 'educational_qualifications'"
            icon="heroicon-o-academic-cap">
            Educational Qualifications
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'conference_training')"
            :active="$activeTab === 'conference_training'"
            icon="heroicon-o-user-group">
            Conferences/Training
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'paper_presentations')"
            :active="$activeTab === 'paper_presentations'"
            icon="heroicon-o-presentation-chart-line">
            Paper Presentations
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'educational_qualifications') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA4\EducationalQualificationsWidget::class)
    </div>

    <div @if($activeTab !== 'conference_training') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA4\ConferenceTrainingWidget::class)
    </div>

    <div @if($activeTab !== 'paper_presentations') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA4\PaperPresentationsWidget::class)
    </div>

</x-filament-panels::page>