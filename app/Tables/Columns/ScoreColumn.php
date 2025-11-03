<?php

namespace App\Tables\Columns;

use App\Models\Submission;
use Filament\Tables\Columns\TextColumn;

class ScoreColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Score')
            ->numeric(2)
            ->description(function (Submission $record) {
                $raw = (float) $record->raw_score;
                $capped = (float) $record->score;
                $lost = $raw - $capped;

                if ($lost > 0.01) {
                    return "Capped at {$capped} (Lost {$lost} pts)";
                }

                return null;
            });
    }
}
