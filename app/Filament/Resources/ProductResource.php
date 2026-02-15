<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use App\Models\PackOption;
use App\Filament\Traits\HasModuleAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ProductResource extends Resource
{
    use HasModuleAccess;

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Store Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₪'),
                Forms\Components\TextInput::make('sale_price')
                    ->numeric(),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_qty')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Current Stock'),
                Forms\Components\TextInput::make('minimum_stock')
                    ->required()
                    ->numeric()
                    ->default(5)
                    ->helperText('Minimum stock level before showing as low stock')
                    ->label('Minimum Stock Level'),
                Forms\Components\Toggle::make('track_inventory')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured Product')
                    ->helperText('Display this product in featured products section')
                    ->required(),
                Forms\Components\TextInput::make('meta_title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pieces_per_package')
                    ->label('Pieces per Pack')
                    ->required()
                    ->numeric()
                    ->default(1),
                
                Forms\Components\Section::make('Category')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(function () {
                                return Category::whereNull('parent_id')
                                    ->active()
                                    ->ordered()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('Select a category')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear sub-category display when category changes
                                $set('sub_category_display', null);
                            })
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('sub_category_display')
                            ->label('Sub-Category')
                            ->readOnly()
                            ->placeholder('Select a category first')
                            ->dehydrated(false),
                        Forms\Components\Select::make('categories')
                            ->label('Assign Categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->helperText('Assign multiple categories to this product')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
                
                Forms\Components\Section::make('Product Images')
                    ->description('Manage product images. The first image will be used as the featured image.')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->relationship()
                            ->schema([
                                Forms\Components\FileUpload::make('path')
                                    ->label('Image')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('products')
                                    ->visibility('public')
                                    ->disk('public')
                                    ->required()
                                    ->columnSpanFull()
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'])
                                    ->maxSize(5120)
                                    ->getUploadedFileNameForStorageUsing(fn ($file): string => (string) str($file->getClientOriginalName())->prepend('product_')),
                                Forms\Components\TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->helperText('Describe the image for accessibility')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Image')
                                    ->default(false)
                                    ->helperText('This image will be used as the main product image'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 
                                !empty($state['alt_text']) ? $state['alt_text'] : 
                                (!empty($state['path']) ? 
                                    (is_array($state['path']) ? 'Uploaded Image' : pathinfo($state['path'], PATHINFO_FILENAME)) 
                                    : 'New Image'))
                            ->addable('Add Image')
                            ->deletable('Remove Image')
                            ->reorderableWithButtons()
                            ->orderable('sort_order')

                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Product Variants')
                    ->description('Manage product variants with different colors, sizes, and pack options. Each variant has its own SKU, price, and stock.')
                    ->schema([
                        Forms\Components\Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('color_id')
                                    ->label('Color')
                                    ->options(Color::pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('Select color (optional)'),
                                Forms\Components\Select::make('size_id')
                                    ->label('Size')
                                    ->options(Size::pluck('name', 'id'))
                                    ->searchable()
                                    ->placeholder('Select size (optional)'),
                                Forms\Components\Select::make('pack_option_id')
                                    ->label('Pack Size')
                                    ->options(PackOption::pluck('label', 'id'))
                                    ->searchable()
                                    ->placeholder('Select pack size (optional)'),
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₪')
                                    ->step(0.01),
                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Sale Price')
                                    ->numeric()
                                    ->prefix('₪')
                                    ->step(0.01),
                                Forms\Components\TextInput::make('stock_qty')
                                    ->label('Stock Quantity')
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\KeyValue::make('attributes')
                                    ->label('Additional Attributes')
                                    ->keyLabel('Attribute Name')
                                    ->valueLabel('Attribute Value')
                                    ->addable()
                                    ->deletable()
                                    ->columnSpanFull(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                Forms\Components\Section::make('Variant Images')
                                    ->description('Upload images specific to this variant')
                                    ->schema([
                                        Forms\Components\Repeater::make('variant_images')
                                            ->schema([
                                                Forms\Components\FileUpload::make('path')
                                                    ->label('Image')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('products/variants')
                                                    ->visibility('public')
                                                    ->disk('public')
                                                    ->required(),
                                                Forms\Components\TextInput::make('alt_text')
                                                    ->label('Alt Text')
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('sort_order')
                                                    ->label('Sort Order')
                                                    ->numeric()
                                                    ->default(0),
                                            ])
                                            ->columns(2)
                                            ->collapsed()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => 
                                                !empty($state['alt_text']) ? $state['alt_text'] : 
                                                (!empty($state['path']) ? 'Variant Image' : 'New Image'))
                                            ->addable('Add Image')
                                            ->deletable('Remove Image'),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->collapsed()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['sku'] ?? 'New Variant')
                            ->addable('Add Variant')
                            ->deletable('Remove Variant'),
                        
                        
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Product Colors')
                    ->description('Define product color variations with optional images (Legacy - use variants instead)')
                    ->schema([
                        Forms\Components\Repeater::make('colors')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Color Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, callable $get) {
                                        // Auto-populate HEX when color name changes
                                        $colorName = $state;
                                        if ($colorName && !empty(trim($colorName))) {
                                            $hex = self::generateHexFromColorName($colorName);
                                            if ($hex) {
                                                $set('hex', $hex);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('hex')
                                    ->label('Hex Color')
                                    ->required()
                                    ->helperText('Auto-populated from color name, or enter manually')
                                    ->maxLength(7)
                                    ->placeholder('#000000')
                                    ->rules([
                                        'regex:/^#[0-9A-Fa-f]{6}$/' // Must be valid hex color format
                                    ])
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, callable $set, callable $get) {
                                        // Auto-populate HEX when color name changes and hex is empty
                                        $colorName = $get('name');
                                        if ($colorName && empty($state)) {
                                            $hex = self::generateHexFromColorName($colorName);
                                            if ($hex) {
                                                $set('hex', $hex);
                                            }
                                        }
                                    }),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Color Image (Optional)')
                                    ->image()
                                    ->directory('product-colors')
                                    ->visibility('public')
                                    ->disk('public')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'New Color')
                            ->orderable('sort_order')
                            ->reorderableWithButtons()
                            ->addable('Add Color')
                            ->deletable('Remove Color'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['featuredImage', 'images']))
            ->columns([
                ImageColumn::make('featured_image_url')
                    ->label('Image')
                    ->size(60)
                    ->circular()
                    ->getStateUsing(function (Product $record): ?string {
                        $featuredImage = $record->featuredImage;
                        if (!$featuredImage) {
                            $featuredImage = $record->images()->first();
                        }
                        return $featuredImage?->url;
                    })
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Product&color=7F9CF5&background=EBF4FF')),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->money('ILS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_qty')
                    ->numeric()
                    ->sortable()
                    ->label('Stock')
                    ->formatStateUsing(function (Product $record): string {
                        if (!$record->track_inventory) {
                            return 'Not Tracked';
                        }
                        
                        $stock = $record->stock_qty;
                        $minimum = $record->minimum_stock ?? 5;
                        
                        if ($stock <= 0) {
                            return "⚠️ {$stock} (Out of Stock)";
                        } elseif ($stock <= $minimum) {
                            return "🟡 {$stock} (Low)";
                        }
                        
                        return "🟢 {$stock}";
                    })
                    ->color(function (Product $record): string {
                        if (!$record->track_inventory) {
                            return 'gray';
                        }
                        
                        $stock = $record->stock_qty;
                        $minimum = $record->minimum_stock ?? 5;
                        
                        if ($stock <= 0) {
                            return 'danger';
                        } elseif ($stock <= $minimum) {
                            return 'warning';
                        }
                        
                        return 'success';
                    }),
                Tables\Columns\IconColumn::make('track_inventory')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),
                Tables\Columns\TextColumn::make('meta_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pieces_per_package')
                    ->label('PCS/Package')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
])
            ->defaultPaginationPageOption(25)
            ->filters([
                //
])
            ->defaultPaginationPageOption(25)
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Status')
                    ->placeholder('All')
                    ->trueLabel('Featured')
                    ->falseLabel('Not Featured'),
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock',
                        'out_of_stock' => 'Out of Stock',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'] === 'in_stock',
                                fn (Builder $query) => $query->where('track_inventory', true)->where('stock_qty', '>', fn ($query) => $query->select('minimum_stock')->from('products as p')->whereColumn('p.id', 'products.id'))
                            )
                            ->when(
                                $data['value'] === 'low_stock',
                                fn (Builder $query) => $query->lowStock()
                            )
                            ->when(
                                $data['value'] === 'out_of_stock',
                                fn (Builder $query) => $query->outOfStock()
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return static::canAccessResource('products');
    }

    /**
     * Generate HEX color from common color names
     */
    private static function generateHexFromColorName(string $colorName): ?string
    {
        $colorMap = [
            // Basic colors
            'red' => '#FF0000',
            'green' => '#008000',
            'blue' => '#0000FF',
            'yellow' => '#FFFF00',
            'orange' => '#FFA500',
            'purple' => '#800080',
            'pink' => '#FFC0CB',
            'brown' => '#A52A2A',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'gray' => '#808080',
            'grey' => '#808080',
            
            // Common variations
            'light blue' => '#ADD8E6',
            'dark blue' => '#00008B',
            'sky blue' => '#87CEEB',
            'navy' => '#000080',
            'light green' => '#90EE90',
            'dark green' => '#006400',
            'lime' => '#32CD32',
            'light red' => '#FF6B6B',
            'dark red' => '#8B0000',
            'maroon' => '#800000',
            'coral' => '#FF7F50',
            'salmon' => '#FA8072',
            'gold' => '#FFD700',
            'silver' => '#C0C0C0',
            'beige' => '#F5F5DC',
            'cream' => '#FFFDD0',
            'ivory' => '#FFFFF0',
            'khaki' => '#F0E68C',
            'tan' => '#D2B48C',
            
            // Popular web colors
            'tomato' => '#FF6347',
            'turquoise' => '#40E0D0',
            'cyan' => '#00FFFF',
            'teal' => '#008080',
            'indigo' => '#4B0082',
            'violet' => '#EE82EE',
            'magenta' => '#FF00FF',
            'fuchsia' => '#FF00FF',
            'lavender' => '#E6E6FA',
            'plum' => '#DDA0DD',
            'orchid' => '#DA70D6',
            
            // Material Design colors
            'amber' => '#FFC107',
            'amber light' => '#FFECB3',
            'amber dark' => '#FFA000',
            'blue grey' => '#607D8B',
            'blue grey light' => '#90A4AE',
            'blue grey dark' => '#37474F',
            'deep orange' => '#FF5722',
            'deep purple' => '#673AB7',
            'light green' => '#8BC34A',
            'light green dark' => '#689F38',
            'lime' => '#CDDC39',
            'lime dark' => '#827717',
            'orange' => '#FF9800',
            'orange dark' => '#F57C00',
            
            // Additional common colors
            'chocolate' => '#D2691E',
            'sienna' => '#A0522D',
            'crimson' => '#DC143C',
            'firebrick' => '#B22222',
            'slate' => '#708090',
            'slate gray' => '#2F4F4F',
            'steel' => '#4682B4',
            'charcoal' => '#36454F',
            'olive' => '#808000',
            'wheat' => '#F5DEB3',
            'peach' => '#FFDAB9',
            'mint' => '#98FF98',
            'aqua' => '#7FFFD4',
        ];
        
        // Convert to lowercase and trim for matching
        $normalizedName = strtolower(trim($colorName));
        
        // Return exact match or null if not found
        return $colorMap[$normalizedName] ?? null;
    }
}
