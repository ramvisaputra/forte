<?php

namespace App\Filament\Widgets;

use App\Models\BarangMasuk;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Auth;

class InComing extends ChartWidget
{
    protected static string $color = 'success';
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Grafik Barang Masuk Tahun ' . now()->year;
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getData(): array
    {
        $data = Trend::model(BarangMasuk::class)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk Bulan '.now()->translatedFormat('F').' ini ',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
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
