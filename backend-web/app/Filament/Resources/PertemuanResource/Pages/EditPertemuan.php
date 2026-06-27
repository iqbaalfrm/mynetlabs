<?php
namespace App\Filament\Resources\PertemuanResource\Pages;
use App\Filament\Resources\PertemuanResource;
use Filament\Resources\Pages\EditRecord;

class EditPertemuan extends EditRecord {
    protected static string $resource = PertemuanResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; }
}