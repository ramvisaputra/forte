<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BarangKeluar;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Exports\CsvExporter;
use Filament\Tables\Actions\ExportAction;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Filament\Resources\LaporanBarangKeluarResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBarangKeluarResource extends Resource
{
    protected static ?string $model = BarangKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Laporan Barang Keluar';
    protected static ?string $pluralModelLabel = 'Laporan Barang Keluar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 22;

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

                Tables\Columns\TextColumn::make('id_keluar')
                    ->label('ID Barang Keluar')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_keluar')
                    ->label('Tanggal Keluar')
                    ->date()
                    ->searchable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jumlah_keluar')
                    ->label('Jumlah Keluar (/Box)'),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR'),
            ])

            ->headerActions([
                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($livewire) {

                        $data = $livewire->getFilteredTableQuery()
                            ->with(['user', 'barang.kategori'])
                            ->get();

                        $pdf = Pdf::loadView('pdf.laporan-barang-keluar', [
                            'data' => $data,
                            'tanggal_cetak' => now()->format('d-m-Y H:i:s'),
                        ])->setPaper('A4', 'landscape');

                        $filename = 'laporan_barang_keluar_' . now()->format('YmdHis') . '.pdf';

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            $filename
                        );
                    })
            ])

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
                    ->query(function (Builder $query, $data) {
                        return $data['value']
                            ? $query->whereYear('tgl_keluar', $data['value'])
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

            ->defaultSort('tgl_keluar', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanBarangKeluars::route('/'),
        ];
    }
}
