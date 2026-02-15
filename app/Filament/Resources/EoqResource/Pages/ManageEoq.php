<?php

namespace App\Filament\Resources\EoqResource\Pages;

use App\Filament\Resources\EoqResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEoqs extends ManageRecords
{
    protected static string $resource = EoqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
