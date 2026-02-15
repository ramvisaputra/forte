<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UpdateTerbaruBarang extends BaseWidget
{
    protected static ?string $heading = 'Update Terbaru Barang';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Barang::query()
            ->with('kategori')
            ->orderByDesc('updated_at')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama_barang')
                ->label('Nama Barang')
                ->searchable(),

            Tables\Columns\TextColumn::make('stok')
                ->label('Stok Saat Ini')
                ->sortable(),

            Tables\Columns\TextColumn::make('kategori.nama_kategori')
                ->label('Kategori')
                ->badge(),

            Tables\Columns\TextColumn::make('updated_at')
                ->label('Terakhir Update')
                ->dateTime('d M Y H:i'),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'petugas';
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
