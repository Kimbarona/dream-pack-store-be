<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class RecentOrdersSimpleWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected static ?string $heading = 'Recent Orders';
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        $dateFrom = request()->get('date_from');
        $dateTo = request()->get('date_to');
        
        return $table
            ->query(
                Order::query()
                    ->when($dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
                    ->when($dateTo, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
                    ->with(['user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->default('Guest'),
                Tables\Columns\TextColumn::make('total')
                    ->money('ILS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'paid_confirmed' => 'success',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state))),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record))
                    ->label('View')
                    ->icon('heroicon-m-eye'),
            ]);
    }
}