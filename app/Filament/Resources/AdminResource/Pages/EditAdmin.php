<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    public function form(Form $form): Form
    {
        return AdminResource::form($form);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only hash password if it's being changed
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncPermissionsFromEnabledModules();
    }
}