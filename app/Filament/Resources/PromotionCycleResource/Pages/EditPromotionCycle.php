<?php

namespace App\Filament\Resources\PromotionCycleResource\Pages;

use App\Filament\Resources\PromotionCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionCycle extends EditRecord
{
    protected static string $resource = PromotionCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
