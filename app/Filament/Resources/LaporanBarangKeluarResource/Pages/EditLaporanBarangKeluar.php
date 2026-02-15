<?php

namespace App\Filament\Resources\LaporanBarangKeluarResource\Pages;

use App\Filament\Resources\LaporanBarangKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanBarangKeluar extends EditRecord
{
    protected static string $resource = LaporanBarangKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
