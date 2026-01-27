<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Filament\Traits\HasModuleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;

class OrderResource extends Resource
{
    use HasModuleAccess;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $label = 'Orders';

    protected static ?string $pluralLabel = 'Orders';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record->order_number ?? ''),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending_payment' => 'Pending Payment',
                                'paid_unconfirmed' => 'Paid (Unconfirmed)',
                                'paid_confirmed' => 'Paid (Confirmed)',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\User::where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->map(fn ($user) => [
                                        'name' => $user->name . ' (' . $user->email . ')',
                                        'value' => $user->id,
                                    ]);
                            })
                            ->getOptionLabelUsing(fn ($value) => 
                                optional(\App\Models\User::find($value), fn ($user) => $user->name)
                            ),
                    ]),

                Section::make('Financial Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->prefix('$')
                            ->numeric()
                            ->formatStateUsing(fn ($record) => number_format($record->subtotal, 2)),

                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->disabled()
                            ->prefix('$')
                            ->numeric()
                            ->formatStateUsing(fn ($record) => number_format($record->tax_amount, 2)),

                        TextInput::make('shipping_amount')
                            ->label('Shipping Amount')
                            ->disabled()
                            ->prefix('$')
                            ->numeric()
                            ->formatStateUsing(fn ($record) => number_format($record->shipping_amount, 2)),

                        TextInput::make('total')
                            ->label('Total')
                            ->disabled()
                            ->prefix('$')
                            ->numeric()
                            ->formatStateUsing(fn ($record) => number_format($record->total, 2)),
                    ]),

                Section::make('Order Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Order Notes')
                            ->rows(3),
                    ]),
            ])
            ->afterSave(function ($record, $data) {
                // Handle status transitions with proper business logic
                if (isset($data['status']) && $data['status'] !== $record->getOriginal('status')) {
                    try {
                        $record->transitionStatus($data['status']);
                    } catch (\Exception $e) {
                        // Log error but don't fail the save
                        \Log::error('Order status transition failed', [
                            'order_id' => $record->id,
                            'old_status' => $record->getOriginal('status'),
                            'new_status' => $data['status'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Order number copied'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->user?->name ?? 'N/A'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->user?->email ?? 'N/A'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'pending_payment' => 'warning',
                        'paid_unconfirmed' => 'info',
                        'paid_confirmed' => 'success',
                        'change' => 'info',
                        'processing' => 'info',
                        'shipped' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->status_label ?? $record->status),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => number_format($record->total, 2)),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'paid_unconfirmed' => 'Paid (Unconfirmed)',
                        'paid_confirmed' => 'Paid (Confirmed)',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search orders...');
    }

    public static function getRelations(): array
    {
        return [
            // Relation managers will be added here when needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return static::canAccessResource('orders');
    }

    public static function canView($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('orders.view');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        return $user && $user->hasPermissionTo('orders.update');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        // Only super admins can delete orders
        return $user && $user->hasPermissionTo('orders.delete') && $user->hasRole('Super Admin');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        // Only super admins can delete orders
        return $user && $user->hasPermissionTo('orders.delete') && $user->hasRole('Super Admin');
    }
}