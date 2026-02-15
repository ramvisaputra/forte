<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
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
            ->whereColumn('stok', '<=', 'stok_minimum');
    }

    /**
     * 🔴 INI KUNCI UTAMA
     */
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
