<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
// We no longer need 'use Filament\Forms\Set;'

class TrimmedNumericInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->numeric()
            ->extraInputAttributes([
                'onkeydown' => "return !['e','E','+','-'].includes(event.key);",
                'inputmode' => 'decimal',
                'onblur' => "this.value = this.value ? parseFloat(this.value) : ''",
            ])
            ->dehydrateStateUsing(
                static fn($state): ?float => ($state === null || $state === '') ? null : (float) $state
            );
    }
}
