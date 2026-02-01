<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\Category;
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
                    ->prefix('$'),
                Forms\Components\TextInput::make('sale_price')
                    ->numeric(),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('track_inventory')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('meta_title')
                    ->maxLength(255),
                Forms\Components\Textarea::make('meta_description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('pieces_per_package')
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
                            ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Auto-populate HEX when color name changes
                                        $colorName = $state;
                                        if ($colorName && !empty(trim($colorName))) {
                                            $hex = self::generateHexFromColorName($colorName);
                                            if ($hex) {
                                                $set('hex', $hex);
                                            }
                                        }
                                    }),
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
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('alt_text')
                                    ->label('Alt Text')
                                    ->helperText('Describe the image for SEO and accessibility')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Image')
                                    ->helperText('Check to make this the featured product image'),
                            ])
                            ->columns(3)
                            ->collapsed()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['alt_text'] ?? 'New Image')
                            ->orderable('sort_order')
                            ->reorderableWithButtons()
                            ->addable('Add Image')
                            ->deletable('Remove Image'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Product Colors')
                    ->description('Define product color variations with optional images.')
                    ->schema([
                        Forms\Components\Repeater::make('colors')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Color Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
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
                                    ->helperText('Auto-populated from color name')
                                    ->maxLength(7)
                                    ->disabled(fn (callable $get): bool => !empty($get('hex'))),
                                Forms\Components\FileUpload::make('image_path')
                                    ->label('Color Image (Optional)')
                                    ->image()
                                    ->imageEditor()
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['featuredImage']))
            ->columns([
                ImageColumn::make('featured_image_url')
                    ->label('Image')
                    ->size(60)
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=Product&color=7F9CF5&background=EBF4FF')),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->money()
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
                    ->sortable(),
                Tables\Columns\IconColumn::make('track_inventory')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
