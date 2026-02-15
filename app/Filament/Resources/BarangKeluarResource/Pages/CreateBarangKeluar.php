<?php

namespace App\Filament\Resources\BarangKeluarResource\Pages;

use App\Models\Barang;
use App\Models\BarangKeluar;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\BarangKeluarResource;

class CreateBarangKeluar extends CreateRecord
{
    protected static string $resource = BarangKeluarResource::class;

    public function getHeading(): string
    {
        return 'Tambah Transaksi Barang Keluar';
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            // 🔐 Lock barang
            $barang = Barang::lockForUpdate()
                ->where('id_barang', $data['id_barang'])
                ->first();

            if (!$barang) {
                Notification::make()
                    ->title('Gagal')
                    ->body('Barang tidak ditemukan')
                    ->danger()
                    ->send();

                abort(422);
            }

            if ($data['jumlah_keluar'] > $barang->stok) {
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->body("Stok tersedia hanya {$barang->stok}")
                    ->danger()
                    ->send();

                abort(422);
            }

            // 🔢 Generate ID
            $lastId = BarangKeluar::lockForUpdate()->max('id_keluar');

            if (!$lastId) {
                $data['id_keluar'] = 'BK001';
            } else {
                $number = (int) substr($lastId, 2) + 1;
                $data['id_keluar'] = 'BK' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }

            // Hitung ulang total harga (server side)
            $data['total_harga'] = $data['jumlah_keluar'] * $barang->harga_keluar;

            // Simpan transaksi
            $barangKeluar = BarangKeluar::create($data);

            // Kurangi stok
            $barang->decrement('stok', $data['jumlah_keluar']);

            // ✅ NOTIFIKASI SUKSES
            Notification::make()
                ->title('Berhasil')
                ->body('Transaksi barang keluar berhasil disimpan')
                ->success()
                ->send();

            return $barangKeluar;
        });
    }
}
