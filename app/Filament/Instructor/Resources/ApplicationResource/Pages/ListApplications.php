<?php

namespace App\Filament\Instructor\Resources\ApplicationResource\Pages;

use App\Filament\Instructor\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Instructor\Widgets\WelcomeWidget;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WelcomeWidget::class,
        ];
    }
}
