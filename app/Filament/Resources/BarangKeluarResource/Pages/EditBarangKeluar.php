<?php

namespace App\Filament\Resources\BarangKeluarResource\Pages;

use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\BarangKeluarResource;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;

class EditBarangKeluar extends EditRecord
{
    protected static string $resource = BarangKeluarResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
{
    DB::transaction(function () use ($data) {

        $record = $this->record->fresh();

        $barang = Barang::lockForUpdate()
            ->where('id_barang', $record->id_barang)
            ->firstOrFail();

        // 🔁 restore stok lama
        $stokAsli = $barang->stok + $record->jumlah_keluar;

        // ❌ validasi
        if ($data['jumlah_keluar'] > $stokAsli) {
            Notification::make()
                ->title('Stok tidak mencukupi')
                ->body("Stok tersedia hanya {$stokAsli} unit.")
                ->danger()
                ->send();

            throw new Halt();
        }

        // ✅ set stok baru
        $barang->stok = $stokAsli - $data['jumlah_keluar'];
        $barang->save();
    });

    return $data;
}
}