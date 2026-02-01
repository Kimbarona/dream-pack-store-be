<?php

namespace App\Providers\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = -1;

    protected static string | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DateFilterWidget::class,
            \App\Filament\Widgets\SimpleStatsWidget::class,
            \App\Filament\Widgets\RevenueChart::class,
            \App\Filament\Widgets\TopProductsWidget::class,
            \App\Filament\Widgets\LowStockAlertWidget::class,
            // \App\Filament\Widgets\RecentOrdersSimpleWidget::class,
        ];
    }
}