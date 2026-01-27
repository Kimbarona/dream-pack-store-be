<?php

namespace App\Filament\Resources\AdminAccountResource\Pages;

use App\Filament\Resources\AdminAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminAccounts extends ListRecords
{
    protected static string $resource = AdminAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
