<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\AttributeValue;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ProductService
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function createProduct(array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($data, $images) {
            // Extract product fields
            $productData = $this->extractProductFields($data);
            
            // Generate meta_description from description
            $productData['meta_description'] = $this->generateMetaDescription($data['description']);
            
            // Create product
            $product = Product::create($productData);
            
            // Attach categories
            if (!empty($data['categories'])) {
                $product->categories()->attach($data['categories']);
            }
            
            // Attach attributes
            if (!empty($data['size'])) {
                $product->attributeValues()->attach($data['size']);
            }
            
            if (!empty($data['colors'])) {
                $product->attributeValues()->attach($data['colors']);
            }
            
            // Handle images
            $this->handleImages($product, $images);
            
            // Log activity
            $this->logActivity('product_created', $product, [
                'title' => $product->title,
                'sku' => $product->sku,
            ]);
            
            return $product;
        });
    }
    
    public function updateProduct(Product $product, array $data, array $images = []): Product
    {
        return DB::transaction(function () use ($product, $data, $images) {
            // Extract product fields
            $productData = $this->extractProductFields($data);
            
            // Check if description has changed, if so regenerate meta_description
            if (isset($data['description']) && $data['description'] !== $product->description) {
                $productData['meta_description'] = $this->generateMetaDescription($data['description']);
            }
            
            // Store old values for logging
            $oldValues = [
                'title' => $product->title,
                'price' => $product->price,
                'sku' => $product->sku,
            ];
            
            // Update product
            $product->update($productData);
            
            // Sync categories
            if (isset($data['categories'])) {
                $product->categories()->sync($data['categories']);
            }
            
            // Sync attributes
            $currentAttributeValues = $product->attributeValues()->pluck('id')->toArray();
            
            // Remove old attributes and add new ones
            $newAttributes = [];
            if (!empty($data['size'])) {
                $newAttributes[] = $data['size'];
            }
            if (!empty($data['colors'])) {
                $newAttributes = array_merge($newAttributes, $data['colors']);
            }
            
            $product->attributeValues()->sync($newAttributes);
            
            // Handle image deletions
            if (!empty($data['delete_images'])) {
                $this->deleteImages($data['delete_images']);
            }
            
            // Handle new image uploads
            $this->handleImages($product, $images);
            
            // Log activity
            $this->logActivity('product_updated', $product, [
                'old' => $oldValues,
                'new' => [
                    'title' => $product->title,
                    'price' => $product->price,
                    'sku' => $product->sku,
                ],
            ]);
            
            return $product;
        });
    }
    
    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            // Log activity before deletion
            $this->logActivity('product_deleted', $product, [
                'title' => $product->title,
                'sku' => $product->sku,
            ]);
            
            // Soft delete product (images and relationships remain)
            $product->delete();
        });
    }
    
    public function restoreProduct(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->restore();
            
            // Log activity
            $this->logActivity('product_restored', $product, [
                'title' => $product->title,
                'sku' => $product->sku,
            ]);
        });
    }
    
    public function reorderProducts(array $products): void
    {
        DB::transaction(function () use ($products) {
            foreach ($products as $productData) {
                Product::where('id', $productData['id'])
                    ->update(['sort_order' => $productData['sort_order']]);
            }
        });
    }
    
    private function extractProductFields(array $data): array
    {
        return [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] ?? null,
            'sku' => $data['sku'],
            'stock_qty' => $data['stock_qty'],
            'track_inventory' => $data['track_inventory'] ?? false,
            'sort_order' => $data['sort_order'],
            'meta_title' => $data['meta_title'] ?? null,
            'pieces_per_package' => $data['pieces_per_package'],
            'is_active' => $data['is_active'] ?? false,
        ];
    }
    
    private function handleImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if ($image instanceof UploadedFile) {
                $path = $image->store('products', 'public');
                
                // Get next sort order
                $nextSort = $product->images()->max('sort_order') + 1;
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'alt_text' => $product->title,
                    'sort_order' => $nextSort,
                ]);
            }
        }
    }
    
    private function deleteImages(array $imageIds): void
    {
        $images = ProductImage::whereIn('id', $imageIds)->get();
        
        foreach ($images as $image) {
            // Delete file from storage
            Storage::disk('public')->delete($image->path);
            
            // Delete database record
            $image->delete();
        }
    }
    
    /**
     * Generate meta_description array from description
     * 
     * @param string $description
     * @return array
     */
    private function generateMetaDescription(string $description): array
    {
        return [
            'en' => $description,
            'he' => $this->translationService->translateEnToHe($description)
        ];
    }

    private function logActivity(string $action, Product $product, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => $action,
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}