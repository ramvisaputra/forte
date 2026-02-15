<?php

namespace App\Filament\Resources\LaporanBarangKeluarResource\Pages;

use App\Filament\Resources\LaporanBarangKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanBarangKeluars extends ListRecords
{
    protected static string $resource = LaporanBarangKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
