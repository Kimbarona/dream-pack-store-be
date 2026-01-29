<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Concerns\HasBackAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditOrder extends EditRecord
{
    use HasBackAction;
    
    protected static string $resource = OrderResource::class;

    protected function afterSave(): void
    {
        // Handle status transitions with proper business logic
        $data = $this->data;
        $record = $this->record;
        
        if (isset($data['status']) && $data['status'] !== $record->getOriginal('status')) {
            try {
                $record->transitionStatus($data['status']);
            } catch (\Exception $e) {
                // Log error but don't fail save
                Log::error('Order status transition failed', [
                    'order_id' => $record->id,
                    'old_status' => $record->getOriginal('status'),
                    'new_status' => $data['status'],
                    'error' => $e->getMessage()
                ]);
                
                // Optionally show a notification to the user
                $this->notify('danger', 'Order status transition failed. Please check logs for details.');
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}
