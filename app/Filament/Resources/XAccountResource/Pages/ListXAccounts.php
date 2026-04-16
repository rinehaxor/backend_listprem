<?php

namespace App\Filament\Resources\XAccountResource\Pages;

use App\Filament\Resources\XAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListXAccounts extends ListRecords
{
    protected static string $resource = XAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
