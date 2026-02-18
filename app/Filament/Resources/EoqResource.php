<?php

namespace App\Filament\Resources;

use App\Models\Barang;
use App\Models\Eoq;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class EoqResource extends Resource
{
    protected static ?string $navigationLabel = 'Perhitungan EOQ';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $slug = 'eoq';
    protected static ?string $pluralModelLabel = 'Perhitungan EOQ';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 20;

    /* ================= AKSES ================= */

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    /* ================= QUERY TABLE ================= */

    public static function getEloquentQuery(): Builder
    {
        return Eoq::query()
            ->with(['barang.kategori']);
        // return Barang::query()
        //     ->leftJoinSub(
        //         DB::table('barang_keluar')
        //             ->select(
        //                 'id_barang',
        //                 DB::raw('SUM(jumlah_keluar) AS permintaan_tahunan')
        //             )
        //             ->groupBy('id_barang'),
        //         'bk',
        //         'barang.id_barang',
        //         '=',
        //         'bk.id_barang'
        //     )
        //     ->select(
        //         'barang.*',
        //         DB::raw('IFNULL(bk.permintaan_tahunan,0) AS permintaan_tahunan'),
        //         DB::raw("
        //             CASE
        //                 WHEN barang.biaya_simpan > 0
        //                      AND IFNULL(bk.permintaan_tahunan,0) > 0
        //                 THEN ROUND(
        //                     SQRT(
        //                         (2 * bk.permintaan_tahunan * barang.biaya_pesan)
        //                         / barang.biaya_simpan
        //                     ), 2
        //                 )
        //                 ELSE 0
        //             END AS eoq
        //         "),
        //         DB::raw("
        //             CASE
        //                 WHEN IFNULL(bk.permintaan_tahunan,0) > 0
        //                     AND (
        //                         CASE
        //                             WHEN barang.biaya_simpan > 0
        //                             THEN SQRT((2 * bk.permintaan_tahunan * barang.biaya_pesan) / barang.biaya_simpan)
        //                             ELSE 0
        //                         END
        //                     ) > 0
        //                 THEN ROUND(
        //                     IFNULL(bk.permintaan_tahunan,0) /
        //                     (
        //                         SQRT((2 * bk.permintaan_tahunan * barang.biaya_pesan) / barang.biaya_simpan)
        //                     ),
        //                 2)
        //                 ELSE 0
        //             END AS frekuensi_pesan
        //         "),

        //         DB::raw("
        //             ROUND(
        //                 IFNULL(bk.permintaan_tahunan,0),
        //             2) AS total_pemesanan
        //     "),

        //     );
    }

    /* ================= TABLE ================= */

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([

                /* ================= HITUNG EOQ ================= */

                Action::make('hitung_eoq')
                    ->label('Hitung & Simpan EOQ')
                    ->icon('heroicon-o-calculator')
                    ->color('success')
                    ->action(function ($livewire) {

                        $filters  = $livewire->tableFilters ?? [];
                        $tahun    = $filters['tahun']['value'] ?? null;
                        $kategori = $filters['id_kategori']['value'] ?? null;
                        $tahunSekarang = now()->year;
                        /* ===== VALIDASI ===== */
                        if (!$tahun) {
                            Notification::make()
                                ->title('Validasi Gagal')
                                ->body('Silakan pilih tahun terlebih dahulu')
                                ->danger()
                                ->send();
                            return;
                        }

                        /* ===== CEK TAHUN BERJALAN / MASA DEPAN ===== */
                        if ($tahun >= $tahunSekarang) {
                            Notification::make()
                                ->title('Perhitungan Ditolak')
                                ->body('EOQ hanya dapat dihitung setelah periode tahun berakhir')
                                ->warning()
                                ->send();
                            return;
                        }

                        /* ===== CEK SUDAH PERNAH DIHITUNG ===== */
                        $sudahDihitung = Eoq::where('tahun', $tahun)
                            ->when($kategori, function ($q) use ($kategori) {
                                $q->whereHas(
                                    'barang',
                                    fn($b) =>
                                    $b->where('id_kategori', $kategori)
                                );
                            })
                            ->exists();

                        if ($sudahDihitung) {
                            Notification::make()
                                ->title('Perhitungan Ditolak')
                                ->body("EOQ tahun {$tahun} sudah pernah dihitung")
                                ->warning()
                                ->send();
                            return;
                        }

                        /* ===== QUERY PERHITUNGAN ===== */
                        $data = Barang::query()
                            ->when(
                                $kategori,
                                fn($q) =>
                                $q->where('id_kategori', $kategori)
                            )
                            ->leftJoinSub(
                                DB::table('barang_keluar')
                                    ->select(
                                        'id_barang',
                                        DB::raw('SUM(jumlah_keluar) AS permintaan_tahunan')
                                    )
                                    ->whereYear('tgl_keluar', $tahun)
                                    ->groupBy('id_barang'),
                                'bk',
                                'barang.id_barang',
                                '=',
                                'bk.id_barang'
                            )
                            ->select(
                                'barang.id_barang',
                                DB::raw('IFNULL(bk.permintaan_tahunan,0) AS permintaan_tahunan'),
                                'barang.biaya_pesan',
                                'barang.biaya_simpan',
                                DB::raw("
                                    CASE
                                        WHEN barang.biaya_simpan > 0
                                             AND IFNULL(bk.permintaan_tahunan,0) > 0
                                        THEN ROUND(
                                            SQRT(
                                                (2 * bk.permintaan_tahunan * barang.biaya_pesan)
                                                / barang.biaya_simpan
                                            ), 2
                                        )
                                        ELSE 0
                                    END AS eoq
                                ")
                            )
                            ->get();

                        /* ===== SIMPAN FINAL ===== */
                        foreach ($data as $row) {
                            $frekuensi = $row->eoq > 0
                                ? round($row->permintaan_tahunan / $row->eoq, 2)
                                : 0;

                            $totalPemesanan = round($frekuensi * $row->eoq, 2);

                            Eoq::create([
                                'id_barang'          => $row->id_barang,
                                'id_kategori'        => $row->id_kategori,
                                'tahun'              => $tahun,
                                'permintaan_tahunan' => $row->permintaan_tahunan,
                                'biaya_pesan'        => $row->biaya_pesan,
                                'biaya_simpan'       => $row->biaya_simpan,
                                'nilai_eoq'          => $row->eoq,
                                'frekuensi_pesan'    => $frekuensi,
                                'total_pemesanan'    => $totalPemesanan,
                            ]);
                        }

                        Notification::make()
                            ->title('Berhasil')
                            ->body("Perhitungan EOQ tahun {$tahun} berhasil disimpan")
                            ->success()
                            ->send();
                    }),

                /* ================= DOWNLOAD CSV (TETAP ADA) ================= */

                Action::make('download_csv')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function ($livewire) {

                        $filters  = $livewire->tableFilters ?? [];
                        $tahun    = $filters['tahun']['value'] ?? now()->year;
                        $kategori = $filters['id_kategori']['value'] ?? null;

                        $filename = 'perhitungan_eoq_' . now()->format('YmdHis') . '.csv';

                        return response()->streamDownload(function () use ($tahun, $kategori) {

                            $file = fopen('php://output', 'w');
                            fwrite($file, "\xEF\xBB\xBF");

                            fputcsv($file, [
                                'ID Barang',
                                'Nama Barang',
                                'Kategori',
                                'Permintaan Tahunan',
                                'Biaya Pesan',
                                'Biaya Simpan',
                                'EOQ',
                                'Frekuensi Pesan',
                                'Total Pemesanan',
                                'Tahun'
                            ]);

                            $query = Barang::query()
                                ->leftJoinSub(
                                    DB::table('barang_keluar')
                                        ->select(
                                            'id_barang',
                                            DB::raw('SUM(jumlah_keluar) AS permintaan_tahunan')
                                        )
                                        ->whereYear('tgl_keluar', $tahun)
                                        ->groupBy('id_barang'),
                                    'bk',
                                    'barang.id_barang',
                                    '=',
                                    'bk.id_barang'
                                )
                                ->leftJoin('kategori', 'barang.id_kategori', '=', 'kategori.id_kategori')
                                ->select(
                                    'barang.id_barang',
                                    'barang.nama_barang',
                                    'kategori.nama_kategori',
                                    DB::raw('IFNULL(bk.permintaan_tahunan,0) AS permintaan_tahunan'),
                                    'barang.biaya_pesan',
                                    'barang.biaya_simpan',
                                    DB::raw("
                                        CASE
                                            WHEN barang.biaya_simpan > 0
                                                 AND IFNULL(bk.permintaan_tahunan,0) > 0
                                            THEN ROUND(
                                                SQRT(
                                                    (2 * bk.permintaan_tahunan * barang.biaya_pesan)
                                                    / barang.biaya_simpan
                                                ), 2
                                            )
                                            ELSE 0
                                        END AS eoq
                                    ")
                                );

                            if ($kategori) {
                                $query->where('barang.id_kategori', $kategori);
                            }

                            foreach ($query->get() as $row) {
                                fputcsv($file, [
                                    $row->id_barang,
                                    $row->nama_barang,
                                    $row->nama_kategori,
                                    $row->permintaan_tahunan,
                                    $row->biaya_pesan,
                                    $row->biaya_simpan,
                                    $row->eoq,
                                    $row->frekuensi_pesan,
                                    $row->total_pemesanan,
                                    $tahun
                                ]);
                            }

                            fclose($file);
                        }, $filename);
                    }),
            ])

            /* ================= KOLOM ================= */
            ->defaultSort(null)
            ->columns([
                Tables\Columns\TextColumn::make('id_barang')->label('ID'),
                Tables\Columns\TextColumn::make('barang.nama_barang')->label('Nama Barang')->searchable(),
                // Tables\Columns\TextColumn::make('kategori.nama_kategori')->label('Kategori'),
                Tables\Columns\TextColumn::make('permintaan_tahunan')->label('Permintaan Tahunan'),
                Tables\Columns\TextColumn::make('biaya_pesan')->money('IDR'),
                Tables\Columns\TextColumn::make('biaya_simpan')->money('IDR'),
                Tables\Columns\TextColumn::make('nilai_eoq')->label('EOQ'),
                Tables\Columns\TextColumn::make('frekuensi_pesan')->label('Frekuensi Pesan (kali/tahun)')->numeric(2),
                Tables\Columns\TextColumn::make('total_pemesanan')->label('Total Pemesanan Tahunan'),

            ])

            /* ================= FILTER ================= */

            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        DB::table('barang_keluar')
                            ->selectRaw('YEAR(tgl_keluar) AS tahun')
                            ->groupBy('tahun')
                            ->orderByDesc('tahun')
                            ->pluck('tahun', 'tahun')
                            ->toArray()
                    )
                    ->query(
                        fn(Builder $q, $data) =>
                        $data['value']
                            ? $q->whereIn('barang.id_barang', function ($sub) use ($data) {
                                $sub->select('id_barang')
                                    ->from('barang_keluar')
                                    ->whereYear('tgl_keluar', $data['value']);
                            })
                            : $q
                    ),

                Tables\Filters\SelectFilter::make('id_kategori')
                    ->label('Kategori')
                    ->options(
                        \App\Models\Kategori::pluck('nama_kategori', 'id_kategori')->toArray()
                    )
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        return $query->whereHas('barang', function ($q) use ($data) {
                            $q->where('id_kategori', $data['value']);
                        });
                    }),

                // ->label('Kategori')
                // ->relationship('kategori', 'nama_kategori'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => EoqResource\Pages\ListEoqs::route('/'),
        ];
    }
}
