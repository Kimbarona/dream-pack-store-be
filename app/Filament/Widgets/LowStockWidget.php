<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalProducts = Product::where('track_inventory', true)->count();
        $lowStockProducts = Product::lowStock()->count();
        $outOfStockProducts = Product::outOfStock()->count();

        return [
            Stat::make('Total Products', $totalProducts)
                ->description('Products tracking inventory')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Low Stock', $lowStockProducts)
                ->description('Products at or below minimum level')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success')
                ->url($lowStockProducts > 0 ? '/admin/products' : null),

            Stat::make('Out of Stock', $outOfStockProducts)
                ->description('Products with zero stock')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockProducts > 0 ? 'danger' : 'success')
                ->url($outOfStockProducts > 0 ? '/admin/products' : null),
        ];
    }
}