<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'performing_art')"
            :active="$activeTab === 'performing_art'"
            icon="heroicon-o-musical-note">
            Performing Art
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'exhibition')"
            :active="$activeTab === 'exhibition'"
            icon="heroicon-o-photo">
            Exhibition
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'juried_design')"
            :active="$activeTab === 'juried_design'"
            icon="heroicon-o-pencil-square">
            Juried Designs
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'literary_publication')"
            :active="$activeTab === 'literary_publication'"
            icon="heroicon-o-book-open">
            Literary Publications
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'performing_art') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\PerformingArtWidget::class)
    </div>

    <div @if($activeTab !== 'exhibition') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\ExhibitionWidget::class)
    </div>

    <div @if($activeTab !== 'juried_design') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\JuriedDesignWidget::class)
    </div>

    <div @if($activeTab !== 'literary_publication') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\LiteraryPublicationWidget::class)
    </div>

</x-filament-panels::page>