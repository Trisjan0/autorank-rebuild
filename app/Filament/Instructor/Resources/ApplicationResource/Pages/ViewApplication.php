<?php

namespace App\Filament\Instructor\Resources\ApplicationResource\Pages;

use App\Filament\Instructor\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    /**
     * Define the infolist to use the one from the resource.
     */
    public function infolist(Infolist $infolist): Infolist
    {
        return static::getResource()::infolist($infolist);
    }

    /**
     * Override the form method to return an empty schema.
     * This forces Filament to use the Infolist for display.
     */
    public function form(Form $form): Form
    {
        return $form->schema([]);
    }
}
