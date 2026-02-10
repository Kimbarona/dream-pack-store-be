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
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HasBackAction;
    
    protected static string $resource = ProductResource::class;

    public function form(Form $form): Form
    {
        return ProductResource::form($form);
    }

    protected function handleRecordUpdate($record, array $data): Product
    {
        return DB::transaction(function () use ($record, $data) {
            // Extract variants data
            $variantsData = $data['variants'] ?? [];
            unset($data['variants']);
            
            // Update slug if needed
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['title']);
            }
            
            // Update product
            $record->update($data);
            
            // Sync categories
            if (isset($data['categories']) && is_array($data['categories'])) {
                $record->categories()->sync($data['categories']);
            }
            
            // Update variants
            $this->updateVariants($record, $variantsData);
            
            // If no variants provided and product has no variants, create default
            if (empty($variantsData) && $record->variants()->count() === 0) {
                $this->createDefaultVariant($record, $data);
            }
            
            return $record;
        });
    }

    private function updateVariants(Product $product, array $variantsData): void
    {
        $existingVariantIds = $product->variants->pluck('id')->toArray();
        $newVariantIds = [];

        foreach ($variantsData as $variantData) {
            $variantId = $variantData['id'] ?? null;
            $imagesData = $variantData['variant_images'] ?? [];
            unset($variantData['variant_images']);
            unset($variantData['id']);

            if ($variantId) {
                // Update existing variant
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->product_id === $product->id) {
                    $variant->update($variantData);
                    $newVariantIds[] = $variantId;
                    
                    // Update variant images
                    $this->updateVariantImages($variant, $imagesData);
                }
            } else {
                // Create new variant
                $variant = $product->variants()->create($variantData);
                $newVariantIds[] = $variant->id;
                
                // Create variant images
                foreach ($imagesData as $imageData) {
                    $variant->images()->create($imageData);
                }
            }
        }

        // Delete variants that are no longer present
        $variantsToDelete = array_diff($existingVariantIds, $newVariantIds);
        if (!empty($variantsToDelete)) {
            ProductVariant::whereIn('id', $variantsToDelete)->delete();
        }
    }

    private function updateVariantImages(ProductVariant $variant, array $imagesData): void
    {
        $existingImageIds = $variant->images->pluck('id')->toArray();
        $newImageIds = [];

        foreach ($imagesData as $imageData) {
            $imageId = $imageData['id'] ?? null;
            unset($imageData['id']);

            if ($imageId) {
                // Update existing image
                $image = ProductVariantImage::find($imageId);
                if ($image && $image->product_variant_id === $variant->id) {
                    $image->update($imageData);
                    $newImageIds[] = $imageId;
                }
            } else {
                // Create new image
                $image = $variant->images()->create($imageData);
                $newImageIds[] = $image->id;
            }
        }

        // Delete images that are no longer present
        $imagesToDelete = array_diff($existingImageIds, $newImageIds);
        if (!empty($imagesToDelete)) {
            ProductVariantImage::whereIn('id', $imagesToDelete)->delete();
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
