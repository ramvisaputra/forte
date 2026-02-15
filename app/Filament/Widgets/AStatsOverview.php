<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;

class AStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Data Barang', Barang::count()),
            Stat::make(
                'Transaksi Barang Masuk Bulan '.now()->translatedFormat('F').' Ini',
                BarangMasuk::whereBetween('tgl_masuk', [now()->startOfMonth(), now()->endOfMonth()])
                ->count()
            ),
            Stat::make(
                'Transaksi Barang Keluar Bulan '.now()->translatedFormat('F').' Ini',
                BarangKeluar::whereBetween('tgl_keluar', [now()->startOfMonth(), now()->endOfMonth()])
                ->count()

            ),
        ];
    }
}
