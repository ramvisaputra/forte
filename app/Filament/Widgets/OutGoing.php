<?php

namespace App\Filament\Widgets;

use App\Models\BarangKeluar;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OutGoing extends ChartWidget
{
    protected static string $color = 'danger';
    protected static ?int $sort = 3;

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = BarangKeluar::selectRaw('YEAR(created_at) as year')
            ->orderByDesc('year')
            ->value('year') ?? now()->year;
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'admin';
    }

    protected function getFilters(): ?array
    {
        return BarangKeluar::query()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year', 'year')
            ->toArray();
    }

    public function getHeading(): string
    {
        $year = $this->filter ?? now()->year;
        return 'Grafik Barang Keluar Tahun ' . $year;
    }

    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        $rawData = \App\Models\BarangKeluar::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('month');

        $data = [];
        $labels = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = \Carbon\Carbon::create()->month($i)->translatedFormat('F');
            $data[] = $rawData[$i]->total ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi Barang Keluar Tahun ' . $year,
                    'data' => $data,
                    // 'backgroundColor' => '#dc2626',
                ],
            ],
            'labels' => $labels,
        ];
    }



    protected function getType(): string
    {
        return 'bar';
    }
}
