<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
            Teaching Effectiveness
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Submit your student and supervisor evaluation scores for each semester.
        </p>

        <form wire:submit.prevent="create" class="mt-6">
            {{ $this->form }}

            <div class="mt-6 text-right">
                <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray">
                    Submit Teaching Effectiveness
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</x-filament-widgets::widget>