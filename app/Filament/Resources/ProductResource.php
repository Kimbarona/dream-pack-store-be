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
                                $set('sub_category_display', null);
                                $categoryId = $state;
                                if ($categoryId) {
                                    $subCategories = Category::where('parent_id', $categoryId)
                                        ->active()
                                        ->ordered()
                                        ->pluck('name', 'id')
                                        ->toArray();
                                    
                                    if (!empty($subCategories)) {
                                        $set('sub_category_display', implode(', ', array_values($subCategories)));
                                    } else {
                                        $set('sub_category_display', 'No sub-categories');
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
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('hex')
                                    ->label('Hex Color')
                                    ->required()
                                    ->helperText('Example: #FF0000')
                                    ->maxLength(7),
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
}
