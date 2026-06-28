<?php

namespace App\Filament\Resources\PertemuanResource\Pages;

use App\Filament\Resources\PertemuanResource;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions;

class ViewPertemuan extends ViewRecord
{
    protected static string $resource = PertemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
