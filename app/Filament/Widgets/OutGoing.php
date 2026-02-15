<?php

namespace App\Filament\Widgets;

use App\Models\BarangKeluar;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Auth;

class OutGoing extends ChartWidget
{
    protected static string $color = 'danger';
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return 'Grafik Barang Keluar Tahun ' . now()->year;
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getData(): array
    {
        $data = Trend::model(BarangKeluar::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count('jumlah_keluar');

        return [
            'datasets' => [
                [
                    'label' => 'Barang Keluar Bulan '.now()->translatedFormat('F').' ini ',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(
                fn(TrendValue $value) =>
                \Carbon\Carbon::parse($value->date)->translatedFormat('F')
            ),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
