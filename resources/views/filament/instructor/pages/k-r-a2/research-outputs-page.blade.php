<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'published_papers')"
            :active="$activeTab === 'published_papers'"
            icon="heroicon-o-document-text">
            Published Papers
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'translated_outputs')"
            :active="$activeTab === 'translated_outputs'"
            icon="heroicon-o-arrows-right-left">
            Translated Outputs
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'citations')"
            :active="$activeTab === 'citations'"
            icon="heroicon-o-chat-bubble-left-right">
            Citations
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'published_papers') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\PublishedPapersWidget::class)
    </div>

    <div @if($activeTab !== 'translated_outputs') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\TranslatedOutputsWidget::class)
    </div>

    <div @if($activeTab !== 'citations') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA2\CitationsWidget::class)
    </div>

</x-filament-panels::page>