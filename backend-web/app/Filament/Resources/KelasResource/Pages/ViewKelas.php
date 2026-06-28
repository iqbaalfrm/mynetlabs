<?php

namespace App\Filament\Resources\KelasResource\Pages;

use App\Filament\Resources\KelasResource;
use Filament\Resources\Pages\ViewRecord;

class ViewKelas extends ViewRecord
{
    protected static string $resource = KelasResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KelasResource\Widgets\KelasStatsWidget::class,
        ];
    }
}
