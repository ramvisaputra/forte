<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BarangMasuk;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Exports\CsvExporter;
use Filament\Tables\Actions\ExportAction;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Filament\Resources\LaporanBarangMasukResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;


class LaporanBarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Laporan Barang Masuk';
    protected static ?string $pluralModelLabel = 'Laporan Barang Masuk';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 21;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'admin';
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

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id_masuk')
                    ->label('ID Barang Masuk')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_masuk')
                    ->label('Tanggal Masuk')
                    ->date()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('jumlah_masuk')
                    ->label('Jumlah Masuk (/Box)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
            ])

            ->headerActions([
                Action::make('download_csv')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {

                        $filename = 'laporan_barang_masuk_' . now()->format('YmdHis') . '.csv';

                        return response()->streamDownload(function () use ($livewire) {

                            $file = fopen('php://output', 'w');
                            fwrite($file, "\xEF\xBB\xBF");

                            fputcsv($file, [
                                'ID Masuk',
                                'User',
                                'Tanggal Masuk',
                                'Nama Barang',
                                'Kategori',
                                'Jumlah Masuk',
                                'Total Harga',
                            ]);

                            /** 🔑 QUERY SUDAH TERFILTER */
                            $query = $livewire->getFilteredTableQuery()
                                ->with(['user', 'barang.kategori']);

                            $query->chunk(200, function ($rows) use ($file) {
                                foreach ($rows as $row) {
                                    fputcsv($file, [
                                        $row->id_masuk,
                                        optional($row->user)->username,
                                        $row->tgl_masuk,
                                        optional($row->barang)->nama_barang,
                                        optional($row->barang?->kategori)->nama_kategori,
                                        $row->jumlah_masuk,
                                        $row->total_harga,
                                    ]);
                                }
                            });

                            fclose($file);
                        }, $filename);
                    })
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(
                        DB::table('barang_masuk')
                            ->selectRaw('YEAR(tgl_masuk) AS tahun')
                            ->groupBy('tahun')
                            ->orderByDesc('tahun')
                            ->pluck('tahun', 'tahun')
                            ->toArray()
                    )
                    ->query(function (Builder $query, $data) {
                        return $data['value']
                            ? $query->whereYear('tgl_masuk', $data['value'])
                            : $query;
                    }),

                Tables\Filters\SelectFilter::make('id_kategori')
                    ->label('Kategori')
                    ->relationship('barang.kategori', 'nama_kategori'),
            ])

            ->paginated([
                10,
                25,
                50,
                100,
            ])

            ->defaultSort('tgl_masuk', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanBarangMasuks::route('/'),
        ];
    }
}
