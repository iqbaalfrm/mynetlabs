<?php
namespace App\Filament\Resources\TopikMateriResource\Pages;
use App\Filament\Resources\TopikMateriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopikMateris extends ListRecords {
    protected static string $resource = TopikMateriResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}