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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
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

    protected static ?string $navigationGroup = 'Store Management';

    protected static ?int $navigationSort = 2;

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
                            ->default('Auto-generated'),

                        Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::fillCustomerAddress($state, $set, $get);
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\User::where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending_payment' => 'Pending Payment',
                                'processing' => 'Processing',
                                'to_ship' => 'To Ship',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending_payment')
                            ->native(false),
                    ]),

                Section::make('Order Items')
                    ->description('Add products to this order')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalculateSubtotal($set, $get);
                            })
                            ->schema([
                                Select::make('product_id')
                                    ->label('Product')
                                    ->required()
                                    ->searchable()
                                    ->getSearchResultsUsing(function (string $search) {
                                        return \App\Models\Product::where('title', 'like', "%{$search}%")
                                            ->orWhere('sku', 'like', "%{$search}%")
                                            ->where('is_active', true)
                                            ->limit(50)
                                            ->get()
                                            ->mapWithKeys(function ($product) {
                                                return [$product->id => $product->title . ' (SKU: ' . $product->sku . ') - $' . number_format($product->price, 2)];
                                            })
                                            ->toArray();
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $product = \App\Models\Product::find($state);
                                        if ($product) {
                                            $set('product_title', $product->title);
                                            $set('product_sku', $product->sku);
                                            $set('unit_price', $product->price);
                                            $set('pieces_per_package', $product->pieces_per_package);
                                            
                                            $quantity = (int) ($get('quantity') ?? 1);
                                            $set('total_price', $product->price * $quantity);
                                        }
                                    }),

                                Hidden::make('product_title'),

                                Hidden::make('product_sku'),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $unitPrice = (float) ($get('unit_price') ?? 0);
                                        $quantity = (int) ($state ?? 1);
                                        $set('total_price', $unitPrice * $quantity);
                                        
                                        // Recalculate subtotal
                                        self::recalculateSubtotal($set, $get);
                                    }),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->prefix('₪')
                                    ->readOnly()
                                    ->required()
                                    ->default(0)
                                    ->dehydrateStateUsing(fn ($state) => (float) ($state ?? 0)),

                                TextInput::make('total_price')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->prefix('₪')
                                    ->disabled()
                                    ->dehydrateStateUsing(fn ($state) => $state ?? 0),

                                TextInput::make('size')
                                    ->label('Size')
                                    ->placeholder('M, L, XL, etc.'),

                                TextInput::make('chosen_color')
                                    ->label('Color')
                                    ->placeholder('Red, Blue, etc.'),

                                TextInput::make('pieces_per_package')
                                    ->label('Pieces per Package')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['product_title']) 
                                    ? $state['product_title'] . ' (Qty: ' . ($state['quantity'] ?? 1) . ')' 
                                    : null
                            )
                            ->addActionLabel('Add Item')
                            ->defaultItems(0),
                    ]),

                Section::make('Financial Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('₪')
                            ->disabled()
                            ->dehydrateStateUsing(fn ($state) => $state ?? 0),

                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('₪')
                            ->default(0)
                            ->disabled()
                            ->helperText('Automatically calculated as 18% of (subtotal + shipping)')
                            ->dehydrateStateUsing(fn ($state) => $state ?? 0),

                        TextInput::make('shipping_amount')
                            ->label('Shipping Amount')
                            ->numeric()
                            ->prefix('₪')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                self::recalculateSubtotal($set, $get);
                            }),

                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->prefix('₪')
                            ->disabled()
                            ->dehydrateStateUsing(fn ($state) => $state ?? 0),

                        Forms\Components\Placeholder::make('calculation_breakdown')
                            ->label('Calculation Breakdown')
                            ->content(function (callable $get) {
                                $subtotal = (float) ($get('subtotal') ?? 0);
                                $shipping = (float) ($get('shipping_amount') ?? 0);
                                $tax = (float) ($get('tax_amount') ?? 0);
                                $total = $subtotal + $tax + $shipping;
                                $taxableAmount = $subtotal + $shipping;
                                
                                return new HtmlString("
                                    <div class='space-y-1 text-sm'>
                                        <div>Subtotal: ₪" . number_format($subtotal, 2) . "</div>
                                        <div>Shipping: ₪" . number_format($shipping, 2) . "</div>
                                        <div class='text-xs text-gray-600'>Tax Base (Subtotal + Shipping): ₪" . number_format($taxableAmount, 2) . "</div>
                                        <div>Tax (18%): ₪" . number_format($tax, 2) . "</div>
                                        <div class='font-semibold border-t pt-1'>Total: ₪" . number_format($total, 2) . "</div>
                                    </div>
                                ");
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Shipping Address')
                    ->columns(2)
                    ->schema([
                        TextInput::make('shipping_address.address_line_1')
                            ->label('Address Line 1')
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('user_id')))
                            ->helperText(fn (callable $get) => !empty($get('user_id')) ? 'Address autofilled from customer profile' : 'Select a customer to autofill address'),
                        TextInput::make('shipping_address.address_line_2')
                            ->label('Address Line 2')
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        TextInput::make('shipping_address.city')
                            ->label('City')
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        TextInput::make('shipping_address.state')
                            ->label('State')
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        TextInput::make('shipping_address.postal_code')
                            ->label('Postal Code')
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        TextInput::make('shipping_address.country')
                            ->label('Country')
                            ->default('US')
                            ->required()
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        TextInput::make('shipping_address.phone')
                            ->label('Phone')
                            ->disabled(fn (callable $get) => !empty($get('user_id'))),
                        
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('clear_customer')
                                ->label('Clear Customer & Enable Manual Address')
                                ->icon('heroicon-o-x-mark')
                                ->color('danger')
                                ->action(function (callable $set, callable $get) {
                                    $set('user_id', null);
                                    $set('shipping_address', [
                                        'address_line_1' => '',
                                        'address_line_2' => '',
                                        'city' => '',
                                        'state' => '',
                                        'postal_code' => '',
                                        'country' => 'US',
                                        'phone' => '',
                                    ]);
                                })
                                ->visible(fn (callable $get) => !empty($get('user_id'))),
                        ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Order Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Order Notes')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['items']))
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
                        'processing' => 'info',
                        'to_ship' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->status_label ?? $record->status),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('ILS')
                    ->sortable(),

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
            ->defaultPaginationPageOption(25)
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_payment' => 'Pending Payment',
                        'processing' => 'Processing',
                        'to_ship' => 'To Ship',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'items']))
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search orders...');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
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
        
        // If no user is authenticated, deny access
        if (!$user) {
            return false;
        }
        
        // Super admins can view all orders
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Regular users can view orders they have permission for
        return $user->hasPermissionTo('orders.view');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // If no user is authenticated, deny access
        if (!$user) {
            return false;
        }
        
        // Super admins can edit all orders
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Regular users can edit orders they have permission for
        return $user->hasPermissionTo('orders.update');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::guard('admin')->user();
        
        // If no user is authenticated, deny access
        if (!$user) {
            return false;
        }
        
        // Super admins can delete all orders
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Regular users can delete orders they have permission for
        return $user->hasPermissionTo('orders.delete');
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::guard('admin')->user();
        
        // If no user is authenticated, deny access
        if (!$user) {
            return false;
        }
        
        // Super admins can delete all orders
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Regular users can delete orders they have permission for
        return $user->hasPermissionTo('orders.delete');
    }

    public static function canCreate(): bool
    {
        $user = Auth::guard('admin')->user();
        
        // If no user is authenticated, deny access
        if (!$user) {
            return false;
        }
        
        // Super admins can create all orders
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        
        // Regular users can create orders they have permission for
        return $user->hasPermissionTo('orders.create');
    }

/**
     * Fill customer address when customer is selected
     */
    private static function fillCustomerAddress($userId, callable $set, callable $get): void
    {
        if (empty($userId)) {
            // Clear address fields when no customer is selected
            $set('shipping_address', [
                'address_line_1' => '',
                'address_line_2' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => 'US',
                'phone' => '',
            ]);
            return;
        }

        // Get customer data
        $customer = \App\Models\User::find($userId);
        
        if ($customer) {
            $shippingAddress = [
                'address_line_1' => $customer->address ?? '',
                'address_line_2' => '', // User model doesn't have address_line_2
                'city' => $customer->city ?? '',
                'state' => '', // User model doesn't have state field
                'postal_code' => $customer->postal_code ?? '',
                'country' => $customer->country ?? 'US',
                'phone' => $customer->phone ?? '',
            ];
            
            $set('shipping_address', $shippingAddress);
        }
    }

/**
     * Recalculate subtotal and tax based on order items and shipping
     */
    private static function recalculateSubtotal(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;
        
        if (is_array($items)) {
            foreach ($items as $item) {
                if (isset($item['total_price'])) {
                    $subtotal += (float) $item['total_price'];
                }
            }
        }
        
        $shipping = (float) ($get('shipping_amount') ?? 0);
        
        // Calculate tax as 18% of (subtotal + shipping)
        $tax = round(($subtotal + $shipping) * 0.18, 2);
        
        // Set calculated values
        $set('subtotal', $subtotal);
        $set('tax_amount', $tax);
        $set('total', $subtotal + $tax + $shipping);
    }
}