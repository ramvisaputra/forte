<?php

namespace App\Filament\Resources\EoqResource\Pages;

use App\Filament\Resources\EoqResource;
use App\Models\Barang;
use App\Models\Eoq;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Filament\Actions;

class ListEoqs extends ListRecords
{
    protected static string $resource = EoqResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\Action::make('hitung')
    //             ->label('Hitung & Simpan EOQ')
    //             ->action(fn () => $this->hitungDanSimpan()),
    //     ];
    // }

    protected function hitungDanSimpan()
    {
        // LOGIC HITUNG EOQ & SIMPAN DB
    }
    // protected function afterTableFiltersUpdated(): void
    // {
    //     $filters = $this->tableFilters ?? [];
    //     $tahun = $filters['tahun']['value'] ?? now()->year;

    //     $query = Barang::query()
    //         ->leftJoinSub(
    //             DB::table('barang_keluars')
    //                 ->select(
    //                     'id_barang',
    //                     DB::raw('SUM(jumlah_keluar) as permintaan_tahunan')
    //                 )
    //                 ->whereYear('tgl_keluar', $tahun)
    //                 ->groupBy('id_barang'),
    //             'bk',
    //             'barangs.id_barang',
    //             '=',
    //             'bk.id_barang'
    //         )
    //         ->select(
    //             'barangs.id_barang',
    //             DB::raw('IFNULL(bk.permintaan_tahunan,0) as permintaan_tahunan'),
    //             'barangs.biaya_pesan',
    //             'barangs.biaya_simpan',
    //             DB::raw("
    //                 CASE
    //                     WHEN barangs.biaya_simpan > 0
    //                          AND IFNULL(bk.permintaan_tahunan,0) > 0
    //                     THEN ROUND(
    //                         SQRT(
    //                             (2 * bk.permintaan_tahunan * barangs.biaya_pesan)
    //                             / barangs.biaya_simpan
    //                         ), 2
    //                     )
    //                     ELSE 0
    //                 END AS eoq
    //             ")
    //         );

    //     if (!empty($filters['id_kategori']['value'] ?? null)) {
    //         $query->where('barangs.id_kategori', $filters['id_kategori']['value']);
    //     }

    //     DB::transaction(function () use ($query, $tahun) {
    //         foreach ($query->get() as $row) {

    //             if ($row->eoq <= 0) continue;

    //             Eoq::updateOrCreate(
    //                 [
    //                     'barang_id' => $row->id_barang,
    //                     'tahun'     => $tahun,
    //                 ],
    //                 [
    //                     'permintaan_tahunan' => $row->permintaan_tahunan,
    //                     'biaya_pesan'        => $row->biaya_pesan,
    //                     'biaya_simpan'       => $row->biaya_simpan,
    //                     'nilai_eoq'          => $row->eoq,
    //                 ]
    //             );
    //         }
    //     });
    // }
}
