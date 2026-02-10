<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing tables
        DB::table('product_variant_images')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_images')->truncate();
        DB::table('product_colors')->truncate();
        DB::table('product_sizes')->truncate();
        DB::table('category_product')->truncate();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        DB::table('colors')->truncate();
        DB::table('sizes')->truncate();
        DB::table('pack_options')->truncate();

        $this->command->info('🧹 Cleared existing catalog data');

        // Seed reference tables
        $this->seedColors();
        $this->seedSizes();
        $this->seedPackOptions();
        $this->seedCategories();

        // Seed products
        $this->seedProducts();

        $this->command->info('✅ Catalog seeded successfully!');
    }

    private function seedColors(): void
    {
        $colors = [
            ['name' => 'Red', 'slug' => 'red', 'hex' => '#EF4444'],
            ['name' => 'Blue', 'slug' => 'blue', 'hex' => '#3B82F6'],
            ['name' => 'Green', 'slug' => 'green', 'hex' => '#22C55E'],
            ['name' => 'Yellow', 'slug' => 'yellow', 'hex' => '#FDE047'],
            ['name' => 'Clear', 'slug' => 'clear', 'hex' => '#F3F4F6'],
            ['name' => 'Black', 'slug' => 'black', 'hex' => '#000000'],
            ['name' => 'Brown', 'slug' => 'brown', 'hex' => '#92400E'],
            ['name' => 'White', 'slug' => 'white', 'hex' => '#FFFFFF'],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert([
                'name' => $color['name'],
                'slug' => $color['slug'],
                'hex' => $color['hex'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('🎨 Seeded ' . count($colors) . ' colors');
    }

    private function seedSizes(): void
    {
        $sizes = [
            ['name' => 'Small', 'slug' => 'small'],
            ['name' => 'Medium', 'slug' => 'medium'],
            ['name' => 'Large', 'slug' => 'large'],
            ['name' => '50um', 'slug' => '50um'],
            ['name' => '75um', 'slug' => '75um'],
            ['name' => 'Small (30x20x20)', 'slug' => 'small-30x20x20'],
            ['name' => 'Medium (40x30x30)', 'slug' => 'medium-40x30x30'],
            ['name' => 'Large (50x40x40)', 'slug' => 'large-50x40x40'],
            ['name' => '500ml', 'slug' => '500ml'],
            ['name' => '1L', 'slug' => '1l'],
            ['name' => '2L', 'slug' => '2l'],
        ];

        foreach ($sizes as $size) {
            DB::table('sizes')->insert([
                'name' => $size['name'],
                'slug' => $size['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('📏 Seeded ' . count($sizes) . ' sizes');
    }

    private function seedPackOptions(): void
    {
        $packOptions = [
            ['value' => 6, 'label' => '6 pcs/pack', 'slug' => '6-pcs-pack'],
            ['value' => 12, 'label' => '12 pcs/pack', 'slug' => '12-pcs-pack'],
            ['value' => 24, 'label' => '24 pcs/pack', 'slug' => '24-pcs-pack'],
            ['value' => 1, 'label' => '1 roll', 'slug' => '1-roll'],
            ['value' => 10, 'label' => '10 boxes', 'slug' => '10-boxes'],
            ['value' => 25, 'label' => '25 boxes', 'slug' => '25-boxes'],
            ['value' => 6, 'label' => '6 containers', 'slug' => '6-containers'],
            ['value' => 12, 'label' => '12 containers', 'slug' => '12-containers'],
        ];

        foreach ($packOptions as $packOption) {
            DB::table('pack_options')->insert([
                'value' => $packOption['value'],
                'label' => $packOption['label'],
                'slug' => $packOption['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('📦 Seeded ' . count($packOptions) . ' pack options');
    }

    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Flexible Packaging',
                'slug' => 'flexible-packaging',
                'description' => 'Versatile packaging solutions for various product types',
                'sort_order' => 1,
                'tag' => 'Bubble Wrap',
            ],
            [
                'name' => 'Rigid Packaging',
                'slug' => 'rigid-packaging',
                'description' => 'Durable and sturdy packaging for shipping and storage',
                'sort_order' => 2,
                'tag' => 'Corrugated Boxes',
            ],
            [
                'name' => 'Bottles & Containers',
                'slug' => 'bottles-containers',
                'description' => 'Various bottles and containers for liquids and storage',
                'sort_order' => 3,
                'tag' => 'Plastic Containers',
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $id = DB::table('categories')->insertGetId([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'sort_order' => $category['sort_order'],
                'tag' => $category['tag'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categoryIds[$category['slug']] = $id;
        }

        $this->command->info('📂 Seeded ' . count($categories) . ' categories');
    }

    private function seedProducts(): void
    {
        // Get reference data
        $categories = DB::table('categories')->pluck('id', 'slug');
        $colors = DB::table('colors')->pluck('id', 'slug');
        $sizes = DB::table('sizes')->pluck('id', 'slug');
        $packOptions = DB::table('pack_options')->pluck('id', 'slug');

        $products = [
            [
                'name' => 'Bubble Wrap',
                'slug' => 'bubble-wrap',
                'base_description' => json_encode([
                    'en' => 'Protective bubble wrap for fragile items during shipping and storage',
                    'he' => 'ניילה בועלות לפריט�ים שברירים למשלוח ואחסון',
                ]),
                'category_id' => $categories['flexible-packaging'],
                'rating' => 4.5,
                'sold_count' => 1247,
                'tag' => 'Bubble Wrap',
                'variants' => [
                    [
                        'color_slug' => 'red',
                        'size_slug' => null,
                        'pack_slug' => '6-pcs-pack',
                        'sku' => 'BW-RED-6',
                        'price' => 24.99,
                        'sale_price' => 19.99,
                        'stock_qty' => 150,
                        'attributes' => json_encode(['thickness' => 'standard', 'bubble_size' => '3/16"']),
                        'images' => [
                            '/images/products/bubble-wrap/red/1.jpg',
                            '/images/products/bubble-wrap/red/2.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'blue',
                        'size_slug' => null,
                        'pack_slug' => '12-pcs-pack',
                        'sku' => 'BW-BLUE-12',
                        'price' => 44.99,
                        'sale_price' => null,
                        'stock_qty' => 85,
                        'attributes' => null,
                        'images' => [
                            '/images/products/bubble-wrap/blue/1.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'clear',
                        'size_slug' => null,
                        'pack_slug' => '24-pcs-pack',
                        'sku' => 'BW-CLEAR-24',
                        'price' => 79.99,
                        'sale_price' => 69.99,
                        'stock_qty' => 200,
                        'attributes' => json_encode(['thickness' => 'heavy-duty']),
                        'images' => [
                            '/images/products/bubble-wrap/clear/1.jpg',
                            '/images/products/bubble-wrap/clear/2.jpg',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Stretch Film',
                'slug' => 'stretch-film',
                'base_description' => json_encode([
                    'en' => 'Durable stretch film for pallet wrapping and load securing',
                    'he' => 'סרטיש נמתיח לעטיפת פלטים ואבטחת מטענים',
                ]),
                'category_id' => $categories['flexible-packaging'],
                'rating' => 4.2,
                'sold_count' => 892,
                'tag' => 'Stretch Film',
                'variants' => [
                    [
                        'color_slug' => 'black',
                        'size_slug' => null,
                        'pack_slug' => '1-roll',
                        'sku' => 'SF-BLK-1',
                        'price' => 89.99,
                        'sale_price' => 74.99,
                        'stock_qty' => 45,
                        'attributes' => json_encode(['gauge' => '80 gauge', 'length' => '1500 ft']),
                        'images' => [
                            '/images/products/stretch-film/black/1.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'clear',
                        'size_slug' => null,
                        'pack_slug' => '1-roll',
                        'sku' => 'SF-CLR-1',
                        'price' => 94.99,
                        'sale_price' => null,
                        'stock_qty' => 62,
                        'attributes' => json_encode(['gauge' => '90 gauge', 'length' => '1500 ft']),
                        'images' => [
                            '/images/products/stretch-film/clear/1.jpg',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Corrugated Boxes',
                'slug' => 'corrugated-boxes',
                'base_description' => json_encode([
                    'en' => 'Strong corrugated cardboard boxes for shipping and storage',
                    'he' => 'קופות גלול מקרטון למשלוח ואחסון',
                ]),
                'category_id' => $categories['rigid-packaging'],
                'rating' => 4.7,
                'sold_count' => 3421,
                'tag' => 'Corrugated Boxes',
                'variants' => [
                    [
                        'color_slug' => 'brown',
                        'size_slug' => 'small-30x20x20',
                        'pack_slug' => '10-boxes',
                        'sku' => 'CB-BRN-SML-10',
                        'price' => 156.99,
                        'sale_price' => null,
                        'stock_qty' => 75,
                        'attributes' => json_encode(['wall_type' => 'single-wall', 'ect' => '32']),
                        'images' => [
                            '/images/products/corrugated-boxes/brown-small/1.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'brown',
                        'size_slug' => 'medium-40x30x30',
                        'pack_slug' => '25-boxes',
                        'sku' => 'CB-BRN-MED-25',
                        'price' => 289.99,
                        'sale_price' => 249.99,
                        'stock_qty' => 50,
                        'attributes' => json_encode(['wall_type' => 'double-wall', 'ect' => '44']),
                        'images' => [
                            '/images/products/corrugated-boxes/brown-medium/1.jpg',
                            '/images/products/corrugated-boxes/brown-medium/2.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'white',
                        'size_slug' => 'large-50x40x40',
                        'pack_slug' => '10-boxes',
                        'sku' => 'CB-WHT-LRG-10',
                        'price' => 344.99,
                        'sale_price' => 299.99,
                        'stock_qty' => 30,
                        'attributes' => json_encode(['wall_type' => 'double-wall', 'ect' => '48']),
                        'images' => [
                            '/images/products/corrugated-boxes/white-large/1.jpg',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Plastic Containers',
                'slug' => 'plastic-containers',
                'base_description' => json_encode([
                    'en' => 'Food-grade plastic containers with secure lids for storage',
                    'he' => 'מכלי פלסטיק למזון מכסים אטומים',
                ]),
                'category_id' => $categories['bottles-containers'],
                'rating' => 4.3,
                'sold_count' => 2156,
                'tag' => 'Plastic Containers',
                'variants' => [
                    [
                        'color_slug' => 'white',
                        'size_slug' => '500ml',
                        'pack_slug' => '6-containers',
                        'sku' => 'PC-WHT-500ML-6',
                        'price' => 34.99,
                        'sale_price' => 29.99,
                        'stock_qty' => 120,
                        'attributes' => json_encode(['material' => 'PET', 'lid_type' => 'screw-cap']),
                        'images' => [
                            '/images/products/plastic-containers/white-500ml/1.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'blue',
                        'size_slug' => '1l',
                        'pack_slug' => '12-containers',
                        'sku' => 'PC-BLU-1L-12',
                        'price' => 89.99,
                        'sale_price' => null,
                        'stock_qty' => 85,
                        'attributes' => json_encode(['material' => 'HDPE', 'lid_type' => 'snap-on']),
                        'images' => [
                            '/images/products/plastic-containers/blue-1l/1.jpg',
                        ],
                    ],
                    [
                        'color_slug' => 'clear',
                        'size_slug' => '2l',
                        'pack_slug' => '6-containers',
                        'sku' => 'PC-CLR-2L-6',
                        'price' => 124.99,
                        'sale_price' => 99.99,
                        'stock_qty' => 60,
                        'attributes' => json_encode(['material' => 'Tritan', 'lid_type' => 'hinged']),
                        'images' => [
                            '/images/products/plastic-containers/clear-2l/1.jpg',
                            '/images/products/plastic-containers/clear-2l/2.jpg',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $this->command->info('📦 Creating product: ' . $productData['name']);

            // Create product
            $baseDesc = json_decode($productData['base_description'], true);
            $productId = DB::table('products')->insertGetId([
                'title' => $productData['name'],
                'slug' => $productData['slug'],
                'description' => $baseDesc['en'], // Use English description for legacy field
                'base_description' => $productData['base_description'],
                'rating' => $productData['rating'],
                'sold_count' => $productData['sold_count'],
                'category_id' => $productData['category_id'],
                'price' => 0, // Will be calculated from variants
                'sale_price' => null,
                'sku' => $productData['variants'][0]['sku'], // Use first variant SKU
                'stock_qty' => 0, // Will be calculated from variants
                'track_inventory' => true,
                'sort_order' => 1,
                'is_active' => true,
                'meta_title' => $productData['name'],
                'meta_description' => strip_tags($productData['base_description']),
                'pieces_per_package' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create variants
            foreach ($productData['variants'] as $variantData) {
                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $productId,
                    'color_id' => $variantData['color_slug'] ? $colors[$variantData['color_slug']] : null,
                    'size_id' => $variantData['size_slug'] ? $sizes[$variantData['size_slug']] : null,
                    'pack_option_id' => $packOptions[$variantData['pack_slug']],
                    'sku' => $variantData['sku'],
                    'price' => $variantData['price'],
                    'sale_price' => $variantData['sale_price'],
                    'stock_qty' => $variantData['stock_qty'],
                    'attributes' => $variantData['attributes'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create variant images
                foreach ($variantData['images'] as $index => $imagePath) {
                    DB::table('product_variant_images')->insert([
                        'product_variant_id' => $variantId,
                        'path' => $imagePath,
                        'alt_text' => $productData['name'] . ' - ' . ($index + 1),
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Attach to category (many-to-many)
            DB::table('category_product')->insert([
                'product_id' => $productId,
                'category_id' => $productData['category_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $variantCount = count($productData['variants']);
            $this->command->info("  ✅ Created $variantCount variants with " . array_sum(array_column($productData['variants'], 'stock_qty')) . " total stock");
        }

        $totalProducts = count($products);
        $totalVariants = array_sum(array_map(fn($p) => count($p['variants']), $products));
        $this->command->info("🎉 Seeded $totalProducts products with $totalVariants total variants");
    }
}