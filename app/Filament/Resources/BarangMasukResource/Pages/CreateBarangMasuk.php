<?php

namespace App\Filament\Resources\BarangMasukResource\Pages;

use App\Filament\Resources\BarangMasukResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBarangMasuk extends CreateRecord
{
    protected static string $resource = BarangMasukResource::class;

    public function getHeading(): string
    {
        return 'Tambah Transaksi Barang Masuk'; // heading custom
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Simpan')
                ->submit('create'),

            Action::make('createAnother')
                ->label('Simpan & Tambah Lagi')
                ->submit('createAnother'),

            Action::make('cancel')
                ->label('Batal')
                ->url($this->getResource()::getUrl())
                ->color('gray'),
        ];
    }

    protected function beforeCreate(): void
    {
        $data = $this->data;

        if (!isset($data['jumlah_masuk']) || $data['jumlah_masuk'] <= 0) {
            \Filament\Notifications\Notification::make()
                ->title('Jumlah masuk tidak valid')
                ->body('Jumlah barang masuk harus lebih dari 0.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
