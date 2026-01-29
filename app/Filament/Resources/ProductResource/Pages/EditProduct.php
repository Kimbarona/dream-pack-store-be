<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HasBackAction;
    
    protected static string $resource = ProductResource::class;

    public function form(Form $form): Form
    {
        return ProductResource::form($form);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['title']) && !isset($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
        }
        
        return $data;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}
