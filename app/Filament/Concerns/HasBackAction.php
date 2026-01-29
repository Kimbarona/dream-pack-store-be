<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Request;

trait HasBackAction
{
    /**
     * Get the back URL for the current page
     */
    protected function backUrl(): string
    {
        // Try to get referer from request headers
        $referer = Request::header('referer');
        
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
            // Check if referer is within the same application
            $appUrl = config('app.url');
            if (str_starts_with($referer, $appUrl)) {
                return $referer;
            }
        }
        
        // Fallback to resource index URL
        return $this->getResourceIndexUrl();
    }
    
    /**
     * Get the resource index URL - override in page classes if needed
     */
    protected function getResourceIndexUrl(): string
    {
        // Default implementation - try to get from static class
        if (method_exists(static::class, 'getResource')) {
            return static::getResource()::getUrl('index');
        }
        
        // Fallback to admin dashboard
        return config('filament.path') ?? '/admin';
    }
    
    /**
     * Create a back action
     */
    protected function backAction(): Action
    {
        return Action::make('back')
            ->label('Back')
            ->icon('heroicon-o-arrow-left')
            ->url($this->backUrl())
            ->color('gray')
            ->extraAttributes([
                'title' => 'Go back to previous page',
            ])
            ->extraAttributes(['class' => 'filament-page-back-action']);
    }
    
    /**
     * Override header actions to include back button
     * Implement this method in pages that need the back button
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
        ];
    }
}