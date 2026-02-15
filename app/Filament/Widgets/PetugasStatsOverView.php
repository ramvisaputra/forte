<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Support\Facades\Auth;

class PetugasStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Barang Masuk Bulan '.now()->translatedFormat('F'),
                BarangMasuk::whereMonth('tgl_masuk', now()->month)->count()

            )->extraAttributes(['class' => 'flex flex-col items-center text-center']),

            Stat::make(
                'Barang Keluar Bulan '.now()->translatedFormat('F'),
                BarangKeluar::whereMonth('tgl_keluar', now()->month)->count()
            )->extraAttributes(['class' => 'flex flex-col items-center text-center']),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'petugas';
    }

    protected function getColumns(): int
    {
        return 2; // dashboard terdiri dari 2 kolom
    }
}
