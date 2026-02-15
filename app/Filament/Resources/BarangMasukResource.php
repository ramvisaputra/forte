<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Barang;
use App\Models\Eoq;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BarangMasuk;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\BarangMasukResource\Pages;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class BarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $pluralModelLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 10;
    public static function canViewAny(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'petugas']);
    }

    public static function canCreate(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'petugas']);
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }

    /* ================= FORM ================= */

    public static function form(Form $form): Form
    {
        return $form->schema([

            /* TANGGAL MASUK */
            Forms\Components\DatePicker::make('tgl_masuk')
                ->label('Tanggal Masuk')
                ->required()
                ->default(now())
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                    $barangId = $get('id_barang');
                    if (!$barangId) return;

                    $tahun = Carbon::parse($state)->year;

                    $eoq = Eoq::where('id_barang', $barangId)
                        ->where('tahun', $tahun)
                        ->first();

                    $set(
                        'info_eoq',
                        $eoq
                            ? 'EOQ ' . $tahun . ' : ' . number_format($eoq->nilai_eoq, 2, ',', '.')
                            : 'EOQ belum dihitung'
                    );
                }),

            Forms\Components\Hidden::make('user_id')
                ->default(fn() => Auth::id())
                ->required(),

            /* PILIH BARANG */
            Forms\Components\Select::make('id_barang')
                ->label('Barang')
                ->options(
                    \App\Models\Barang::all()->mapWithKeys(function ($barang) {
                        return [$barang->id_barang => "{$barang->id_barang} - {$barang->nama_barang}"];
                    })
                )
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                    if (!$state) {
                        $set('info_eoq', 'EOQ belum dihitung');
                        $set('harga_barang', null);
                        return;
                    }

                    // Harga barang
                    $barang = Barang::where('id_barang', $state)->first();
                    if ($barang) {
                        $set('harga_barang', $barang->harga_masuk);
                    }

                    // 🔥 AMBIL EOQ TERBARU (BUKAN BERDASARKAN TANGGAL)
                    $eoq = Eoq::where('id_barang', $state)
                        ->orderByDesc('tahun')
                        ->first();

                    $set(
                        'info_eoq',
                        $eoq
                            ? 'Perhitungan EOQ Tahun ' . $eoq->tahun . ' : ' . number_format($eoq->nilai_eoq, 2, ',', '.')
                            : 'EOQ belum dihitung'
                    );
                }),

            /* INFO EOQ */
            Forms\Components\TextInput::make('info_eoq')
                ->label('Informasi EOQ')
                ->disabled()
                ->dehydrated(false)
                ->reactive()
                ->default('EOQ belum dihitung'),

            Forms\Components\TextInput::make('harga_barang')
                ->label('Harga Barang')
                ->numeric()
                ->disabled()
                ->dehydrated(false),

            Forms\Components\TextInput::make('jumlah_masuk')
                ->label('Jumlah Masuk (/Box)')
                ->numeric()
                ->required()
                ->minValue(1)
                ->rule('gt:0')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {

                    // 🔔 Notifikasi jika input 0 atau kurang
                    if ($state !== null && $state <= 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Jumlah masuk tidak valid')
                            ->body('Jumlah barang masuk harus lebih dari 0.')
                            ->warning()
                            ->send();

                        // Reset ke nilai aman
                        $set('jumlah_masuk', 1);
                        return;
                    }
                    $barang = Barang::where('id_barang', $get('id_barang'))->first();

                    if ($barang) {
                        $set('total_harga', $state * $barang->harga_masuk);
                    }
                }),

            Forms\Components\TextInput::make('total_harga')
                ->label('Total Harga')
                ->numeric()
                ->disabled()
        ]);
    }

    /* ================= TABLE ================= */

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id_masuk')
                ->label('Id Masuk')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('tgl_masuk')
                ->label('Tanggal Masuk')
                ->date()
                ->date('j M Y')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Dicatat Oleh')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('barang.nama_barang')->label('Nama Barang')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('jumlah_masuk')->label('Jumlah Masuk (/Box)')->sortable(),
            Tables\Columns\TextColumn::make('total_harga')->money('IDR'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {

                        DB::beginTransaction();

                        $barang = $record->barang;

                        // Validasi stok
                        if (($barang->stok - $record->jumlah_masuk) < 0) {

                            DB::rollBack();

                            Notification::make()
                                ->title('Penghapusan Gagal')
                                ->body('Stok tidak boleh kurang dari 0')
                                ->danger()
                                ->send();

                            // Hentikan proses delete
                            throw new \Filament\Support\Exceptions\Halt();
                        }

                        // Kurangi stok
                        $barang->decrement('stok', $record->jumlah_masuk);

                        DB::commit();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBarangMasuks::route('/'),
            'create' => Pages\CreateBarangMasuk::route('/create'),
            'view'   => Pages\ViewBarangMasuk::route('/{record}'),
            'edit'   => Pages\EditBarangMasuk::route('/{record}/edit'),
        ];
    }
}
