<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class LowStockAlertWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Cache low stock query for performance
        $lowStockProducts = Cache::remember('low-stock-products', 300, function () {
            return Product::where('track_inventory', true)
                ->where('stock_qty', '<=', 10)
                ->where('is_active', true)
                ->orderBy('stock_qty', 'asc')
                ->take(5)
                ->get(['id', 'title', 'stock_qty']);
        });
        
        $stats = [];
        
        foreach ($lowStockProducts as $product) {
            $stockLevel = $product->stock_qty;
            $color = $stockLevel <= 2 ? 'danger' : ($stockLevel <= 5 ? 'warning' : 'info');
            $icon = $stockLevel <= 2 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-exclamation-circle';
            
            $stats[] = Stat::make("{$product->title}", $product->stock_qty)
                ->description("{$product->stock_qty} left")
                ->descriptionIcon($icon)
                ->color($color)
                ->url(route('filament.admin.resources.products.edit', $product->id));
        }
        
        // Add summary stat if there are low stock items
        $totalLowStock = $lowStockProducts->count();
        if ($totalLowStock > 0) {
            array_unshift($stats, 
                Stat::make('Low Stock Items', $totalLowStock)
                    ->description('Need to reorder')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger')
            );
        }
        
        return $stats;
    }
    
    protected function getColumns(): int
    {
        return $this->getStats() ? 2 : 1;
    }
    
    protected function getDescription(): ?string
    {
        $totalLowStock = Product::where('track_inventory', true)
            ->where('stock_qty', '<=', 10)
            ->where('is_active', true)
            ->count();
            
        return $totalLowStock > 0 
            ? 'Products running low on inventory. Reorder soon to avoid stockouts.'
            : 'All products have sufficient stock levels.';
    }
    
    protected function getHeading(): string
    {
        return 'Low Stock Alerts';
    }
    
    protected function getIcon(): ?string
    {
        return 'heroicon-m-cube';
    }
    
    protected function getHeadingIcon(): ?string
    {
        return 'heroicon-m-exclamation-triangle';
    }
}