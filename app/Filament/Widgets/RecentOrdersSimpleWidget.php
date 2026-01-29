<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class RecentOrdersSimpleWidget extends Widget
{
    protected static ?int $sort = 4;
    
    protected static ?string $heading = 'Recent Orders';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static string $view = 'filament.widgets.recent-orders-simple';
    
    public function getRecentOrders()
    {
        $dateFrom = request()->get('date_from');
        $dateTo = request()->get('date_to');
        
        return Order::query()
            ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();
    }
    
    public function getStatusColor($status)
    {
        return match($status) {
            'pending_payment' => 'warning',
            'paid_confirmed' => 'success',
            'processing' => 'info',
            'shipped' => 'primary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }
}