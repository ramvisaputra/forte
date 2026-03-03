<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use App\Models\Eoq;
use Filament\Tables;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PeringatanStokMinimum extends TableWidget
{
    protected static ?string $heading = 'Peringatan Stok Minimum';

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected static ?int $sort = 4;

    protected function getTableQuery(): Builder
    {
        return Barang::query()
            ->with('eoqs')
            ->whereColumn('stok', '<=', 'stok_minimum');
    }
    public function getTableRecordKey($record): string
    {
        return $record->id_barang;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Nama Barang')
                ->searchable(),

            Tables\Columns\TextColumn::make('stok')
                ->label('Stok Saat Ini')
                ->sortable()
                ->color('danger'),

            Tables\Columns\TextColumn::make('stok_minimum')
                ->label('Stok Minimum'),

            Tables\Columns\TextColumn::make('jumlah_pemesanan_optimal')
                ->label('Jumlah Pemesanan Optimal')
                ->getStateUsing(function ($record) {
                    $eoq = $record->eoqs->sortByDesc('tahun')->first();

                    if (! $eoq || empty($eoq->nilai_eoq)) {
                        return '-';
                    }

                    return number_format(ceil($eoq->nilai_eoq), 0) . ' box';
                })
                ->badge()
                ->color('success'),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('kategori')
                ->relationship('kategori', 'nama_kategori'),

            Tables\Filters\Filter::make('stok_minimum')
                ->query(
                    fn(Builder $query) =>
                    $query->whereColumn('stok', '<=', 'stok_minimum')
                ),
        ];
    }
}
