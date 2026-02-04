<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStats extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columns = 4;
    
    protected function getCards(): array
    {
        $dateFrom = request()->get('date_from');
        $dateTo = request()->get('date_to');
        
        // Set default date range if not provided
        if (!$dateFrom) {
            $dateFrom = now()->startOfMonth()->toDateString();
        }
        if (!$dateTo) {
            $dateTo = now()->endOfDay()->toDateString();
        }
        
        // Convert to Carbon instances
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();
        
        // Total Products (all time)
        $totalProducts = Product::count();
        
        // Active Products
        $activeProducts = Product::where('is_active', true)->count();
        
        // Total Orders in date range
        $totalOrders = Order::whereBetween('created_at', [$from, $to])->count();
        
        // Total Revenue in date range (only from paid orders)
        $totalRevenue = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['paid_confirmed', 'processing', 'shipped'])
            ->sum('total');
        
        // Orders by status for additional insights
        $pendingOrders = Order::whereBetween('created_at', [$from, $to])
            ->where('status', 'pending_payment')
            ->count();
            
        $paidOrders = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['paid_confirmed', 'processing', 'shipped'])
            ->count();
        
        return [
            Stat::make('Total Products', number_format($totalProducts))
                ->description($activeProducts . ' active')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
                
            Stat::make('Total Orders', number_format($totalOrders))
                ->description($pendingOrders . ' pending')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
                
            Stat::make('Paid Orders', number_format($paidOrders))
                ->description('From ' . $from->format('M j') . ' to ' . $to->format('M j'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Total Revenue', '₪' . number_format($totalRevenue, 2))
                ->description('In selected date range')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}