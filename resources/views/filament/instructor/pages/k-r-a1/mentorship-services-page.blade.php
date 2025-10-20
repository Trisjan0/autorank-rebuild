<x-filament-panels::page>

    <x-filament::tabs wire:model.live="activeTab">
        <x-filament::tabs.item
            wire:click="$set('activeTab', 'adviser')"
            :active="$activeTab === 'adviser'"
            icon="heroicon-o-academic-cap">
            As Adviser
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'panel_member')"
            :active="$activeTab === 'panel_member'"
            icon="heroicon-o-user-group">
            As Panel Member
        </x-filament::tabs.item>

        <x-filament::tabs.item
            wire:click="$set('activeTab', 'mentorship')"
            :active="$activeTab === 'mentorship'"
            icon="heroicon-o-sparkles">
            Mentorship (Competitions)
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div @if($activeTab !== 'adviser') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\AdviserWidget::class)
    </div>

    <div @if($activeTab !== 'panel_member') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\PanelMemberWidget::class)
    </div>

    <div @if($activeTab !== 'mentorship') hidden @endif>
        @livewire(\App\Filament\Instructor\Widgets\KRA1\MentorshipWidget::class)
    </div>

</x-filament-panels::page>