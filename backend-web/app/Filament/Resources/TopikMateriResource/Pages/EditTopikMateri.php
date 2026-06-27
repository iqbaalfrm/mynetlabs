<?php
namespace App\Filament\Resources\TopikMateriResource\Pages;
use App\Filament\Resources\TopikMateriResource;
use Filament\Resources\Pages\EditRecord;

class EditTopikMateri extends EditRecord {
    protected static string $resource = TopikMateriResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\DeleteAction::make()]; }
}