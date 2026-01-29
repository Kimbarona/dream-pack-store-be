<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\ProductColor;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Sample product data
        $products = [
            [
                'title' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-t-shirt',
                'description' => 'Premium quality 100% cotton t-shirt, comfortable and breathable for everyday wear.',
                'price' => 29.99,
                'sale_price' => 24.99,
                'sku' => 'TSH001',
                'stock_qty' => 100,
                'pieces_per_package' => 1,
                'category_slug' => 'clothing',
                'sizes' => ['S', 'M', 'L', 'XL'],
                'colors' => [
                    ['name' => 'White', 'hex' => '#FFFFFF'],
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Blue', 'hex' => '#0000FF'],
                ],
                'image_count' => 4,
            ],
            [
                'title' => 'Slim Fit Denim Jeans',
                'slug' => 'slim-fit-denim-jeans',
                'description' => 'Modern slim fit jeans with stretch comfort, perfect for any occasion.',
                'price' => 79.99,
                'sku' => 'JNS001',
                'stock_qty' => 50,
                'pieces_per_package' => 1,
                'category_slug' => 'clothing',
                'sizes' => ['M', 'L', 'XL', 'XXL'],
                'colors' => [
                    ['name' => 'Blue', 'hex' => '#1E3A8A'],
                    ['name' => 'Black', 'hex' => '#000000'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Leather Wallet',
                'slug' => 'leather-wallet',
                'description' => 'Genuine leather wallet with multiple card slots and RFID protection.',
                'price' => 49.99,
                'sku' => 'WL001',
                'stock_qty' => 75,
                'pieces_per_package' => 1,
                'category_slug' => 'accessories',
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Brown', 'hex' => '#8B4513'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Summer Floral Dress',
                'slug' => 'summer-floral-dress',
                'description' => 'Beautiful floral print dress perfect for summer occasions, lightweight and comfortable.',
                'price' => 89.99,
                'sale_price' => 69.99,
                'sku' => 'DRES001',
                'stock_qty' => 30,
                'pieces_per_package' => 1,
                'category_slug' => 'clothing',
                'sizes' => ['XS', 'S', 'M', 'L'],
                'colors' => [
                    ['name' => 'Red', 'hex' => '#FF0000'],
                    ['name' => 'Yellow', 'hex' => '#FFFF00'],
                ],
                'image_count' => 4,
            ],
            [
                'title' => 'Cotton Backpack',
                'slug' => 'cotton-backpack',
                'description' => 'Durable cotton canvas backpack with multiple compartments, perfect for daily use.',
                'price' => 39.99,
                'sku' => 'BKP001',
                'stock_qty' => 60,
                'pieces_per_package' => 1,
                'category_slug' => 'accessories',
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'Green', 'hex' => '#00FF00'],
                    ['name' => 'Blue', 'hex' => '#0000FF'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Silver Necklace Set',
                'slug' => 'silver-necklace-set',
                'description' => 'Elegant sterling silver necklace with pendant, comes in gift box.',
                'price' => 129.99,
                'sku' => 'JWL001',
                'stock_qty' => 20,
                'pieces_per_package' => 2,
                'category_slug' => 'jewelry',
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Silver', 'hex' => '#C0C0C0'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Kids Cotton T-Shirt',
                'slug' => 'kids-cotton-t-shirt',
                'description' => 'Soft and comfortable 100% cotton t-shirt for kids, available in fun colors.',
                'price' => 19.99,
                'sku' => 'KIDS001',
                'stock_qty' => 80,
                'pieces_per_package' => 3,
                'category_slug' => 'clothing',
                'sizes' => ['XS', 'S', 'M'],
                'colors' => [
                    ['name' => 'Blue', 'hex' => '#0000FF'],
                    ['name' => 'Green', 'hex' => '#00FF00'],
                    ['name' => 'Yellow', 'hex' => '#FFFF00'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Luxury Bedding Set',
                'slug' => 'luxury-bedding-set',
                'description' => 'Premium quality bedding set including sheets, pillowcases, and duvet cover.',
                'price' => 199.99,
                'sku' => 'BED001',
                'stock_qty' => 25,
                'pieces_per_package' => 4,
                'category_slug' => 'home',
                'sizes' => ['Queen', 'King'],
                'colors' => [
                    ['name' => 'White', 'hex' => '#FFFFFF'],
                    ['name' => 'Purple', 'hex' => '#800080'],
                ],
                'image_count' => 4,
            ],
            [
                'title' => 'Wireless Headphones',
                'slug' => 'wireless-headphones',
                'description' => 'Premium noise-cancelling wireless headphones with superior sound quality.',
                'price' => 249.99,
                'sale_price' => 199.99,
                'sku' => 'HP001',
                'stock_qty' => 40,
                'pieces_per_package' => 1,
                'category_slug' => 'electronics',
                'sizes' => ['One Size'],
                'colors' => [
                    ['name' => 'Black', 'hex' => '#000000'],
                    ['name' => 'White', 'hex' => '#FFFFFF'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Yoga Mat',
                'slug' => 'yoga-mat',
                'description' => 'Non-slip exercise yoga mat with carrying strap, perfect for home or studio use.',
                'price' => 34.99,
                'sku' => 'YOG001',
                'stock_qty' => 90,
                'pieces_per_package' => 1,
                'category_slug' => 'sports',
                'sizes' => ['Standard'],
                'colors' => [
                    ['name' => 'Purple', 'hex' => '#800080'],
                    ['name' => 'Blue', 'hex' => '#0000FF'],
                    ['name' => 'Green', 'hex' => '#00FF00'],
                ],
                'image_count' => 3,
            ],
            [
                'title' => 'Ceramic Coffee Mug Set',
                'slug' => 'ceramic-coffee-mug-set',
                'description' => 'Set of 4 handcrafted ceramic coffee mugs, dishwasher and microwave safe.',
                'price' => 44.99,
                'sku' => 'MUG001',
                'stock_qty' => 55,
                'pieces_per_package' => 4,
                'category_slug' => 'home',
                'sizes' => ['Standard'],
                'colors' => [
                    ['name' => 'White', 'hex' => '#FFFFFF'],
                    ['name' => 'Blue', 'hex' => '#4169E1'],
                ],
                'image_count' => 3,
            ],
        ];

        // Get sample images
        $sampleImages = glob(database_path('seeders/assets/product_*.jpg'));
        
        foreach ($products as $index => $productData) {
            // Create product
            $product = Product::create([
                'title' => $productData['title'],
                'slug' => $productData['slug'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'sale_price' => $productData['sale_price'] ?? null,
                'sku' => $productData['sku'],
                'stock_qty' => $productData['stock_qty'],
                'track_inventory' => true,
                'sort_order' => $index + 1,
                'is_active' => true,
                'meta_title' => $productData['title'],
                'meta_description' => Str::limit($productData['description'], 160),
                'pieces_per_package' => $productData['pieces_per_package'],
            ]);

            // Attach to category if exists
            $category = Category::where('slug', $productData['category_slug'])->first();
            if ($category) {
                $product->categories()->attach($category->id);
            }

            // Create sizes
            foreach ($productData['sizes'] as $sizeIndex => $sizeValue) {
                ProductSize::create([
                    'product_id' => $product->id,
                    'value' => $sizeValue,
                    'sort_order' => $sizeIndex,
                ]);
            }

            // Create colors
            foreach ($productData['colors'] as $colorIndex => $colorData) {
                ProductColor::create([
                    'product_id' => $product->id,
                    'name' => $colorData['name'],
                    'hex' => $colorData['hex'],
                    'sort_order' => $colorIndex,
                ]);
            }

            // Create product images with file copying
            $this->createProductImages($product, $productData['image_count'], $sampleImages);

            $this->command->info("✓ Created product: {$product->title} with {$productData['image_count']} images");
        }

        $this->command->info('✅ Products seeded successfully!');
    }

    private function createProductImages(Product $product, int $imageCount, array $sampleImages): void
    {
        // Create product directory in storage
        $productDir = "products/{$product->id}/gallery";
        Storage::disk('public')->makeDirectory($productDir);

        for ($i = 0; $i < $imageCount; $i++) {
            // Select a random sample image
            $sourceImage = $sampleImages[array_rand($sampleImages)];
            $sourceFilename = basename($sourceImage);
            
            // Generate unique filename
            $uniqueFilename = uniqid() . '_' . $sourceFilename;
            $destinationPath = $productDir . '/' . $uniqueFilename;
            
            // Copy file to storage
            $sourceContent = file_get_contents($sourceImage);
            Storage::disk('public')->put($destinationPath, $sourceContent);
            
            // Create database record
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $destinationPath,
                'alt_text' => "{$product->title} - Image " . ($i + 1),
                'sort_order' => $i,
                'is_featured' => $i === 0, // First image is featured
            ]);
        }
    }
}