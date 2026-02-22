<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class TopProductsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;
    
    protected function getStats(): array
    {
        // Cache top products query for performance
        $topProducts = Cache::remember('top-products-30-days', 300, function () {
            return Product::select('products.id', 'products.title', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('orders.status', ['processing', 'to_ship', 'shipped', 'delivered'])
                ->where('orders.created_at', '>=', now()->subDays(30))
                ->where('products.is_active', true)
                ->groupBy('products.id', 'products.title')
                ->orderBy('total_sold', 'desc')
                ->limit(5)
                ->get();
        });
        
        $stats = [];
        $maxSold = $topProducts->first()?->total_sold ?? 0;
        
        foreach ($topProducts as $index => $product) {
            $sold = $product->total_sold;
            $percentage = $maxSold > 0 ? ($sold / $maxSold) * 100 : 0;
            
            $stats[] = Stat::make($product->title, $sold)
                ->description(number_format($sold) . ' sold')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary')
                ->chart([
                    'type' => 'line',
                    'data' => [$sold],
                    'color' => '#10b981',
                ])
                ->extraAttributes([
                    'title' => 'Sold ' . number_format($sold) . ' units (Top ' . ($index + 1) . ')',
                ])
                ->url(route('filament.admin.resources.products.edit', $product->id));
        }
        
        return $stats;
    }
    
    protected function getDescription(): ?string
    {
        $totalSold = Product::join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['processing', 'to_ship', 'shipped', 'delivered'])
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->sum('order_items.quantity');
            
        return $totalSold > 0 
            ? 'Best-selling products in the last 30 days. Click any product to view details.'
            : 'No sales data available for the selected period.';
    }
    
    protected function getHeading(): string
    {
        return 'Top Products';
    }
    
    protected function getIcon(): ?string
    {
        return 'heroicon-m-trending-up';
    }
    
    protected function getHeadingIcon(): ?string
    {
        return 'heroicon-m-chart-bar';
    }
    
    protected function getColumns(): int
    {
        return 2;
    }
    

}