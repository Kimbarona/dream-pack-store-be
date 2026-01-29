<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SimpleStatsWidget extends Widget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Store Statistics';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static string $view = 'filament.widgets.simple-stats';
    
    public function getData()
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
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $paidOrders,
            'fromDate' => $from,
            'toDate' => $to,
        ];
    }
}