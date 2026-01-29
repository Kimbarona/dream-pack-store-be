<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;
    
    protected static ?string $heading = 'Revenue Overview';
    
    protected static ?string $maxHeight = '300px';
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        $dateFrom = request()->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = request()->get('date_to', now()->toDateString());
        
        $from = Carbon::parse($dateFrom);
        $to = Carbon::parse($dateTo);
        
        // Get daily revenue data
        $revenueData = Order::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['paid_confirmed', 'processing', 'shipped'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill missing dates with zero
        $period = $from->daysUntil($to->addDay());
        $labels = [];
        $revenue = [];
        $orders = [];
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $dayData = $revenueData->where('date', $dateStr)->first();
            
            $labels[] = $date->format('M j');
            $revenue[] = $dayData ? (float) $dayData->revenue : 0;
            $orders[] = $dayData ? (int) $dayData->orders : 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Revenue ($)',
                    'data' => $revenue,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Orders',
                    'data' => $orders,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue ($)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Orders',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}