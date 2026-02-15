<?php

namespace App\Filament\Resources;

use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Tables;
use App\Models\Barang;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\BarangKeluar;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BarangKeluarResource\Pages;
use App\Filament\Resources\BarangKeluarResource\RelationManagers;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;
use Filament\Support\Exceptions\Halt;

class BarangKeluarResource extends Resource
{
    protected static ?string $model = BarangKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationLabel = 'Barang Keluar';

    protected static ?string $pluralModelLabel = 'Barang Keluar';

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 11;
    // Menu tampil
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check();
    }

    // Bisa lihat
    public static function canViewAny(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'petugas']);
    }

    // Bisa tambah
    public static function canCreate(): bool
    {
        return in_array(Auth::user()->role, ['admin', 'petugas']);
    }

    // TIDAK BOLEH EDIT
    public static function canEdit($record): bool
    {
        return true;
    }

    // TIDAK BOLEH DELETE
    public static function canDelete($record): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tgl_keluar')
                    ->label('Tanggal Keluar')
                    ->required()
                    ->rule('date')
                    ->default(now()),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id())
                    ->required(),
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
                    ->afterStateUpdated(function ($state, callable $set) {
                        $barang = \App\Models\Barang::where('id_barang', $state)->first();

                        if ($barang) {
                            $set('harga_barang', $barang->harga_keluar);
                            $set('total_harga', null);
                            $set('stok_barang', $barang->stok);
                        }
                    }),

                Forms\Components\TextInput::make('stok_barang')
                    ->label('Stok Tersedia')
                    ->disabled()
                    ->dehydrated(false)
                    ->reactive()
                    ->default(''),

                Forms\Components\TextInput::make('harga_barang')
                    ->label('Harga Barang')
                    ->numeric()
                    ->disabled()
                    // ->readOnly()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('jumlah_keluar')
                    ->label('Jumlah Keluar (/Box)')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->rule('gt:0')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {

                        $barangId = $get('id_barang');

                        if (!$barangId) {
                            return;
                        }

                        $barang = \App\Models\Barang::where('id_barang', $barangId)->first();

                        if (!$barang) {
                            return;
                        }

                        // 🔔 Jika jumlah keluar 0 atau kurang
                        if ($state !== null && $state <= 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Jumlah keluar tidak valid')
                                ->body('Jumlah barang keluar harus lebih dari 0.')
                                ->warning()
                                ->send();

                            $set('jumlah_keluar', 1);
                            return;
                        }

                        // 🔔 Jika jumlah keluar melebihi stok
                        // if ($state > $barang->stok) {
                        //     \Filament\Notifications\Notification::make()
                        //         ->title('Stok tidak mencukupi')
                        //         ->body("Stok tersedia hanya {$barang->stok} unit.")
                        //         ->danger()
                        //         ->send();

                        //     $set('jumlah_keluar', $barang->stok);
                        //     return;
                        // }

                        // Hitung total harga
                        $set('total_harga', $state * $barang->harga_keluar);
                    }),

                // ->afterStateUpdated(function ($state, callable $set, callable $get) {

                //     $barangId = $get('id_barang');

                //     if (!$barangId) return;

                //     $barang = \App\Models\Barang::where('id_barang', $barangId)->first();

                //     if ($barang && $state > $barang->stok) {
                //         $set('jumlah_keluar', $barang->stok);
                //     }

                //     if ($barang) {
                //         $set('total_harga', $state * $barang->harga_keluar);
                //     }
                // }),

                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->numeric()
                    ->required()
                    ->live()
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_keluar')
                    ->label('Id Keluar')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tgl_keluar')
                    ->label('Tanggal Keluar')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->date('j M Y'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barang.nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_keluar')
                    ->label('Jumlah Keluar (/Box)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {

                        DB::transaction(function () use ($record) {

                            $barang = Barang::lockForUpdate()
                                ->where('id_barang', $record->id_barang)
                                ->first();

                            if ($barang) {
                                // 🔁 Kembalikan stok
                                $barang->stok += $record->jumlah_keluar;
                                $barang->save();
                            }
                        });
                    })
                    ->after(function () {
                        Notification::make()
                            ->title('Berhasil')
                            ->body('Transaksi barang keluar dihapus dan stok dikembalikan')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangKeluars::route('/'),
            'create' => Pages\CreateBarangKeluar::route('/create'),
            'view' => Pages\ViewBarangKeluar::route('/{record}'),
            'edit' => Pages\EditBarangKeluar::route('/{record}/edit'),
        ];
    }
}
