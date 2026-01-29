<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    use HasBackAction;
    
    protected static string $resource = CategoryResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}