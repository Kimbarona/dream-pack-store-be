<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmin extends ViewRecord
{
    use HasBackAction;
    
    protected static string $resource = AdminResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}