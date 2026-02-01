<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Dream Pack Admin')
            ->colors([
                'primary' => '#f59e0b', // Simple amber color
            ])
            ->pages([
                \App\Providers\Filament\Pages\Dashboard::class,
                // \App\Filament\Pages\GeneralSettings::class, // Hidden temporarily
            ])
            ->resources([
                \App\Filament\Resources\AdminResource::class,
                \App\Filament\Resources\UserResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\OrderResource::class,
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\SubCategoryResource::class,
                \App\Filament\Resources\BannerResource::class,
            ])
            ->widgets([
                \App\Filament\Widgets\DateFilterWidget::class,
                \App\Filament\Widgets\SimpleStatsWidget::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\TopProductsWidget::class,
                \App\Filament\Widgets\LowStockAlertWidget::class,
            ])
            ->authGuard('admin')
            ->middleware([
                'web',
                'redirect.if.not.admin',
            ]);
    }
}