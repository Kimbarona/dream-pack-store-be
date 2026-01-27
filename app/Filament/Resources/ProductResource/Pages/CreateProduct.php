<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function form(Form $form): Form
    {
        return ProductResource::form($form);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['slug']) && isset($data['title'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }
        
        // Set default values
        $data['track_inventory'] = $data['track_inventory'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['pieces_per_package'] = $data['pieces_per_package'] ?? 1;
        
        return $data;
    }
}
