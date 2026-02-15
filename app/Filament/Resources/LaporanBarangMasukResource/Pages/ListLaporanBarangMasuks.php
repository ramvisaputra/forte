<?php

namespace App\Filament\Resources\LaporanBarangMasukResource\Pages;

use App\Filament\Resources\LaporanBarangMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanBarangMasuks extends ListRecords
{
    protected static string $resource = LaporanBarangMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
