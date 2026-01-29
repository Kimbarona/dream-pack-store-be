<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    use HasBackAction;
    
    protected static string $resource = BannerResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}