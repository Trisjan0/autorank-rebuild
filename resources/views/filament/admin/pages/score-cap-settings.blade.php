<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="flex justify-end items-center gap-4 mt-6">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">
                    Save Changes
                </span>

                <span wire:loading wire:target="save">
                    Saving...
                </span>
            </x-filament::button>

            <x-filament::button
                type="button"
                color="danger"
                wire:click="resetToDefaults"
                wire:loading.attr="disabled"
                wire:target="resetToDefaults"
            >
                Reset to Default
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>