<?php

namespace App\Filament\Resources\KraWeightResource\Pages;

use App\Filament\Resources\KraWeightResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKraWeights extends ListRecords
{
    protected static string $resource = KraWeightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
