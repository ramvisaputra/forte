<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Eoq;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EoqService
{
    public static function hitungDanSimpan(string $barangId, int $tahun): void
    {
        $barang = Barang::find($barangId);
        if (!$barang) return;

        $permintaanTahunan = DB::table('barang_keluars')
            ->where('id_barang', $barangId)
            ->whereYear('tgl_keluar', $tahun)
            ->sum('jumlah_keluar');

        if ($permintaanTahunan <= 0 || $barang->biaya_simpan <= 0) {
            return;
        }

        $eoq = sqrt(
            (2 * $permintaanTahunan * $barang->biaya_pesan)
            / $barang->biaya_simpan
        );

        Eoq::updateOrCreate(
            [
                'barang_id' => $barangId,
                'tahun' => $tahun,
            ],
            [
                'permintaan_tahunan' => $permintaanTahunan,
                'biaya_pesan' => $barang->biaya_pesan,
                'biaya_simpan' => $barang->biaya_simpan,
                'nilai_eoq' => round($eoq, 2),
            ]
        );
        Log::info('EOQ SERVICE DIPANGGIL', [
            'barang_id' => $barangId,
            'tahun' => $tahun,
        ]);
    }
}
