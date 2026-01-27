<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number if not provided
        if (empty($data['order_number'])) {
            $data['order_number'] = 'ORD-' . strtoupper(uniqid());
        }

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = 'pending_payment';
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('index'))
                ->extraAttributes(fn (): array => [
                    'onclick' => "if (document.referrer && document.referrer.includes(window.location.origin)) { window.history.back(); return false; }",
                ]),
        ];
    }
}
