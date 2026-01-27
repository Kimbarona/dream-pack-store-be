<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\AttributeValue;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
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
                'category_slug' => 'men-t-shirts',
                'size' => 'medium',
                'colors' => ['white', 'black', 'blue'],
                'images' => [
                    ['path' => 'products/tshirt-1.jpg', 'alt_text' => 'Classic Cotton T-Shirt - Front'],
                    ['path' => 'products/tshirt-2.jpg', 'alt_text' => 'Classic Cotton T-Shirt - Back'],
                ]
            ],
            [
                'title' => 'Slim Fit Denim Jeans',
                'slug' => 'slim-fit-denim-jeans',
                'description' => 'Modern slim fit jeans with stretch comfort, perfect for any occasion.',
                'price' => 79.99,
                'sku' => 'JNS001',
                'stock_qty' => 50,
                'pieces_per_package' => 1,
                'category_slug' => 'men-pants',
                'size' => 'large',
                'colors' => ['blue', 'black'],
                'images' => [
                    ['path' => 'products/jeans-1.jpg', 'alt_text' => 'Slim Fit Denim Jeans'],
                ]
            ],
            [
                'title' => 'Leather Wallet',
                'slug' => 'leather-wallet',
                'description' => 'Genuine leather wallet with multiple card slots and RFID protection.',
                'price' => 49.99,
                'sku' => 'WL001',
                'stock_qty' => 75,
                'pieces_per_package' => 1,
                'category_slug' => 'wallets',
                'size' => 'small',
                'colors' => ['black', 'brown'],
                'images' => [
                    ['path' => 'products/wallet-1.jpg', 'alt_text' => 'Leather Wallet'],
                ]
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
                'category_slug' => 'women-dresses',
                'size' => 'medium',
                'colors' => ['red', 'yellow'],
                'images' => [
                    ['path' => 'products/dress-1.jpg', 'alt_text' => 'Summer Floral Dress'],
                    ['path' => 'products/dress-2.jpg', 'alt_text' => 'Summer Floral Dress - Back'],
                ]
            ],
            [
                'title' => 'Cotton Backpack',
                'slug' => 'cotton-backpack',
                'description' => 'Durable cotton canvas backpack with multiple compartments, perfect for daily use.',
                'price' => 39.99,
                'sku' => 'BKP001',
                'stock_qty' => 60,
                'pieces_per_package' => 1,
                'category_slug' => 'backpacks',
                'size' => 'large',
                'colors' => ['black', 'green', 'blue'],
                'images' => [
                    ['path' => 'products/backpack-1.jpg', 'alt_text' => 'Cotton Backpack'],
                ]
            ],
            [
                'title' => 'Silver Necklace Set',
                'slug' => 'silver-necklace-set',
                'description' => 'Elegant sterling silver necklace with pendant, comes in gift box.',
                'price' => 129.99,
                'sku' => 'JWL001',
                'stock_qty' => 20,
                'pieces_per_package' => 2,
                'category_slug' => 'necklaces',
                'size' => 'medium',
                'colors' => ['white'],
                'images' => [
                    ['path' => 'products/necklace-1.jpg', 'alt_text' => 'Silver Necklace Set'],
                ]
            ],
            [
                'title' => 'Kids Cotton T-Shirt',
                'slug' => 'kids-cotton-t-shirt',
                'description' => 'Soft and comfortable 100% cotton t-shirt for kids, available in fun colors.',
                'price' => 19.99,
                'sku' => 'KIDS001',
                'stock_qty' => 80,
                'pieces_per_package' => 3,
                'category_slug' => 'boys',
                'size' => 'small',
                'colors' => ['blue', 'green', 'yellow'],
                'images' => [
                    ['path' => 'products/kids-tshirt-1.jpg', 'alt_text' => 'Kids Cotton T-Shirt'],
                ]
            ],
            [
                'title' => 'Luxury Bedding Set',
                'slug' => 'luxury-bedding-set',
                'description' => 'Premium quality bedding set including sheets, pillowcases, and duvet cover.',
                'price' => 199.99,
                'sku' => 'BED001',
                'stock_qty' => 25,
                'pieces_per_package' => 4,
                'category_slug' => 'bedding',
                'size' => 'large',
                'colors' => ['white', 'purple'],
                'images' => [
                    ['path' => 'products/bedding-1.jpg', 'alt_text' => 'Luxury Bedding Set'],
                ]
            ]
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category_slug'])->first();
            
            $product = Product::create([
                'title' => $productData['title'],
                'slug' => $productData['slug'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'sale_price' => $productData['sale_price'] ?? null,
                'sku' => $productData['sku'],
                'stock_qty' => $productData['stock_qty'],
                'track_inventory' => true,
                'sort_order' => 0,
                'is_active' => true,
                'meta_title' => $productData['title'],
                'meta_description' => $productData['description'],
                'pieces_per_package' => $productData['pieces_per_package'],
            ]);

            if ($category) {
                $product->categories()->attach($category->id);
            }

            // Attach size attribute
            $sizeValue = AttributeValue::where('slug', $productData['size'])->first();
            if ($sizeValue) {
                $product->attributeValues()->attach($sizeValue->id);
            }

            // Attach color attributes
            foreach ($productData['colors'] as $colorSlug) {
                $colorValue = AttributeValue::where('slug', $colorSlug)->first();
                if ($colorValue) {
                    $product->attributeValues()->attach($colorValue->id);
                }
            }

            // Add images
            foreach ($productData['images'] as $index => $imageData) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $imageData['path'],
                    'alt_text' => $imageData['alt_text'],
                    'sort_order' => $index,
                ]);
            }
        }
    }
}