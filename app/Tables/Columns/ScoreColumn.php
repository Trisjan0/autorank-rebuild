<?php

namespace App\Tables\Columns;

use App\Models\Application;
use App\Models\Submission;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class ScoreColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Score')
            ->numeric(2)
            ->description(function (Model $record) {

                $submission = null;

                if ($record instanceof Application) {
                    $submission = $record->submission;
                } elseif ($record instanceof Submission) {
                    $submission = $record;
                }

                if (! $submission) {
                    return null;
                }

                $raw = (float) $submission->raw_score;
                $capped = (float) $submission->score;
                $lost = $raw - $capped;

                if ($lost > 0.01) {
                    return "Capped at {$capped} (Lost {$lost} pts)";
                }

                return null;
            });
    }
}
