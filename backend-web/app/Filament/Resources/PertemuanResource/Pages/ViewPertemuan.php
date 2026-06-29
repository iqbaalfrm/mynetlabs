<?php

namespace App\Filament\Resources\PertemuanResource\Pages;

use App\Filament\Resources\PertemuanResource;
use Filament\Resources\Pages\ViewRecord;

use Filament\Actions;

use Filament\Resources\Pages\Concerns\HasRelationManagers;

class ViewPertemuan extends ViewRecord
{
    use HasRelationManagers;

    protected static string $resource = PertemuanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
