<?php

namespace App\Filament\Resources\HasilKuisResource\Pages;

use App\Filament\Resources\HasilKuisResource;
use Filament\Resources\Pages\ListRecords;

class ListHasilKuis extends ListRecords
{
    protected static string $resource = HasilKuisResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Hapus tombol tambah agar murni read-only
    }
}
