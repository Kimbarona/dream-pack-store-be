<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    public function form(Form $form): Form
    {
        return AdminResource::form($form);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = bcrypt($data['password'] ?? 'password');
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissionsFromEnabledModules();
    }
}