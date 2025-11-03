<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
// We no longer need 'use Filament\Forms\Set;'

class TrimmedIntegerInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->integer()
            ->extraInputAttributes([
                'onblur' => "this.value = this.value ? parseInt(this.value, 10) : ''",
            ])
            ->dehydrateStateUsing(
                static fn($state): ?int => ($state === null || $state === '') ? null : (int) $state
            );
    }
}
