<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BarangResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BarangResource\RelationManagers;
use Filament\Forms\Components\Select;
use PhpParser\Node\Stmt\Label;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Barang';

    protected static ?string $pluralModelLabel = 'Barang';

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 4;
    // Tampilkan menu
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    // Bisa lihat data
    public static function canViewAny(): bool
    {
        return Auth::user()->role === 'admin';
    }

    // CRUD
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->role === 'admin';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_kategori')
                    ->label('Kategori')
                    ->options(Kategori::where('status', 'aktif')->pluck('nama_kategori', 'id_kategori'))
                    ->searchable()
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('nama_barang')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('harga_masuk')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->nullable(),

                Forms\Components\TextInput::make('harga_keluar')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->nullable(),

                Forms\Components\TextInput::make('biaya_pesan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->required(),

                Forms\Components\TextInput::make('biaya_simpan')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->required(),

                Forms\Components\TextInput::make('stok_minimum')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->required(),

                Forms\Components\TextInput::make('stok')
                    ->label('Stok (/Box)')
                    ->numeric()
                    ->integer()
                    ->readOnly()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_barang')
                    ->label('Id Barang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama_kategori')
                    ->label('Nama Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_masuk')
                    ->label('Harga Masuk')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_keluar')
                    ->label('Harga Keluar')
                    ->money('IDR')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('leadtime')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('biaya_pesan')
                    ->label('Biaya Pesan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('biaya_simpan')
                    ->label('Biaya Simpan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok_minimum')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok (/Box)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Penghapusan Tidak Diizinkan')
                    ->modalDescription(function ($record) {

                        $jumlahMasuk  = $record->barangMasuks()->count();
                        $jumlahKeluar = $record->barangKeluars()->count();

                        if ($jumlahMasuk > 0 || $jumlahKeluar > 0) {
                            return "Barang ini tidak dapat dihapus karena sudah memiliki riwayat transaksi.";
                        }

                        return 'Apakah Anda yakin ingin menghapus barang ini?';
                    })
                    // ->disabled(
                    //     fn($record) =>
                    //     $record->barangMasuks()->exists() ||
                    //         $record->barangKeluars()->exists()
                    // ),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'view' => Pages\ViewBarang::route('/{record}'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
