<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Concerns\HasBackAction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Models\PackOption;
use Illuminate\Support\Facades\DB;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HasBackAction;
    
    protected static string $resource = ProductResource::class;

    public function form(Form $form): Form
    {
        return ProductResource::form($form);
    }

    protected function handleRecordCreation(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Extract variants data
            $variantsData = $data['variants'] ?? [];
            unset($data['variants']);
            
            // Set default values
            if (!isset($data['slug']) && isset($data['title'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
            }
            
            $data['track_inventory'] = $data['track_inventory'] ?? true;
            $data['is_active'] = $data['is_active'] ?? true;
            $data['pieces_per_package'] = $data['pieces_per_package'] ?? 1;
            
            // Create the product
            $product = Product::create($data);
            
            // Attach categories if provided
            if (isset($data['categories']) && is_array($data['categories'])) {
                $product->categories()->attach($data['categories']);
            }
            
            // Create variants
            $this->createVariants($product, $variantsData);
            
            // If no variants were provided, create a default variant
            if (empty($variantsData)) {
                $this->createDefaultVariant($product, $data);
            }
            
            return $product;
        });
    }

    private function createVariants(Product $product, array $variantsData): void
    {
        foreach ($variantsData as $variantData) {
            $imagesData = $variantData['variant_images'] ?? [];
            unset($variantData['variant_images']);
            
            $variant = $product->variants()->create($variantData);
            
            // Create variant images
            foreach ($imagesData as $imageData) {
                $variant->images()->create($imageData);
            }
        }
    }

    private function createDefaultVariant(Product $product, array $productData): void
    {
        // Find pack option based on product's pieces_per_package
        $packOption = PackOption::where('value', $product->pieces_per_package ?? 1)->first() 
                    ?? PackOption::where('value', 1)->first();
        
        $product->variants()->create([
            'color_id' => null,
            'size_id' => null,
            'pack_option_id' => $packOption?->id,
            'sku' => $productData['sku'] ?? $product->slug . '-default',
            'price' => $productData['price'] ?? 0,
            'sale_price' => $productData['sale_price'] ?? null,
            'stock_qty' => $productData['stock_qty'] ?? 0,
            'is_active' => true,
        ]);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            $this->backAction(),
            ...parent::getHeaderActions(),
        ];
    }
}
