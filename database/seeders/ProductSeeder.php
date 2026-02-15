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
                'id' => 1,
                'name' => 'Bubble Wrap',
                'slug' => 'bubble-wrap',
                'baseDescription' => [
                    'en' => 'Protective bubble wrap for fragile items during shipping and storage',
                    'he' => 'ניילה בועלות לפריט ים שברירים למשלוח ואחסון'
                ],
                'category' => [
                    'id' => 1,
                    'categoryName' => 'Flexible Packaging',
                    'description' => 'Versatile packaging solutions for various product types',
                    'parent' => null,
                    'tag' => 'Bubble Wrap'
                ],
                'rating' => 4.5,
                'soldCount' => 1247,
                'price' => 24.99,
                'salePrice' => 19.99,
                'options' => [
                    'colors' => [],
                    'sizes' => [],
                    'pcsPerPack' => [
                        [
                            'id' => '1',
                            'name' => '1 roll',
                            'value' => 1,
                            'label' => '1 roll'
                        ]
                    ]
                ],
                'variants' => [
                    [
                        'id' => '12',
                        'variantId' => 'bubble-wrap-default-default-1',
                        'color' => null,
                        'colorHex' => null,
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 1,
                        'packSizeId' => 1,
                        'sku' => 'BW-RED-6',
                        'price' => 24.99,
                        'salePrice' => 19.99,
                        'inStock' => true,
                        'stock' => 10,
                        'images' => [],
                        'attributes' => null
                    ]
                ],
                'parent' => 'Flexible Packaging',
                'tag' => 'Bubble Wrap',
                'createdAt' => '2026-02-10T11:04:36Z',
                'updatedAt' => '2026-02-12T07:10:55Z'
            ],
            [
                'id' => 3,
                'name' => 'Corrugated Boxes',
                'slug' => 'corrugated-boxes',
                'baseDescription' => [
                    'en' => 'Strong corrugated cardboard boxes for shipping and storage',
                    'he' => 'קופות גלול מקרטון למשלוח ואחסון'
                ],
                'category' => [
                    'id' => 2,
                    'categoryName' => 'Rigid Packaging',
                    'description' => 'Durable and sturdy packaging for shipping and storage',
                    'parent' => null,
                    'tag' => 'Corrugated Boxes'
                ],
                'rating' => 4.7,
                'soldCount' => 3421,
                'price' => 156.99,
                'salePrice' => 249.99,
                'options' => [
                    'colors' => [
                        [
                            'id' => 'brown',
                            'name' => 'Brown',
                            'value' => 'brown',
                            'hex' => '#92400E'
                        ],
                        [
                            'id' => 'white',
                            'name' => 'White',
                            'value' => 'white',
                            'hex' => '#FFFFFF'
                        ]
                    ],
                    'sizes' => [],
                    'pcsPerPack' => [
                        [
                            'id' => '10',
                            'name' => '10 boxes',
                            'value' => 10,
                            'label' => '10 boxes'
                        ],
                        [
                            'id' => '25',
                            'name' => '25 boxes',
                            'value' => 25,
                            'label' => '25 boxes'
                        ]
                    ]
                ],
                'variants' => [
                    [
                        'id' => '6',
                        'variantId' => 'corrugated-boxes-brown-default-10',
                        'color' => 'Brown',
                        'colorHex' => '#92400E',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 10,
                        'packSizeId' => 10,
                        'sku' => 'CB-BRN-SML-10',
                        'price' => 156.99,
                        'salePrice' => null,
                        'inStock' => true,
                        'stock' => 75,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-small/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-small/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'wall_type' => 'single-wall',
                            'ect' => '32'
                        ]
                    ],
                    [
                        'id' => '7',
                        'variantId' => 'corrugated-boxes-brown-default-25',
                        'color' => 'Brown',
                        'colorHex' => '#92400E',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 25,
                        'packSizeId' => 25,
                        'sku' => 'CB-BRN-MED-25',
                        'price' => 289.99,
                        'salePrice' => 249.99,
                        'inStock' => true,
                        'stock' => 50,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-medium/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-medium/1.jpg'
                            ],
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-medium/2.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/corrugated-boxes/brown-medium/2.jpg'
                            ]
                        ],
                        'attributes' => [
                            'wall_type' => 'double-wall',
                            'ect' => '44'
                        ]
                    ],
                    [
                        'id' => '8',
                        'variantId' => 'corrugated-boxes-white-default-10',
                        'color' => 'White',
                        'colorHex' => '#FFFFFF',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 10,
                        'packSizeId' => 10,
                        'sku' => 'CB-WHT-LRG-10',
                        'price' => 344.99,
                        'salePrice' => 299.99,
                        'inStock' => true,
                        'stock' => 30,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/corrugated-boxes/white-large/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/corrugated-boxes/white-large/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'wall_type' => 'double-wall',
                            'ect' => '48'
                        ]
                    ]
                ],
                'parent' => 'Rigid Packaging',
                'tag' => 'Corrugated Boxes',
                'createdAt' => '2026-02-10T11:04:36Z',
                'updatedAt' => '2026-02-10T11:04:36Z'
            ],
            [
                'id' => 4,
                'name' => 'Plastic Containers',
                'slug' => 'plastic-containers',
                'baseDescription' => [
                    'en' => 'Food-grade plastic containers with secure lids for storage',
                    'he' => 'מכלי פלסטיק למזון מכסים אטומים'
                ],
                'category' => [
                    'id' => 3,
                    'categoryName' => 'Bottles & Containers',
                    'description' => 'Various bottles and containers for liquids and storage',
                    'parent' => null,
                    'tag' => 'Plastic Containers'
                ],
                'rating' => 4.3,
                'soldCount' => 2156,
                'price' => 34.99,
                'salePrice' => 29.99,
                'options' => [
                    'colors' => [
                        [
                            'id' => 'white',
                            'name' => 'White',
                            'value' => 'white',
                            'hex' => '#FFFFFF'
                        ],
                        [
                            'id' => 'blue',
                            'name' => 'Blue',
                            'value' => 'blue',
                            'hex' => '#3B82F6'
                        ],
                        [
                            'id' => 'clear',
                            'name' => 'Clear',
                            'value' => 'clear',
                            'hex' => '#F3F4F6'
                        ]
                    ],
                    'sizes' => [],
                    'pcsPerPack' => [
                        [
                            'id' => '6',
                            'name' => '6 containers',
                            'value' => 6,
                            'label' => '6 containers'
                        ],
                        [
                            'id' => '12',
                            'name' => '12 containers',
                            'value' => 12,
                            'label' => '12 containers'
                        ]
                    ]
                ],
                'variants' => [
                    [
                        'id' => '9',
                        'variantId' => 'plastic-containers-white-default-6',
                        'color' => 'White',
                        'colorHex' => '#FFFFFF',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 6,
                        'packSizeId' => 6,
                        'sku' => 'PC-WHT-500ML-6',
                        'price' => 34.99,
                        'salePrice' => 29.99,
                        'inStock' => true,
                        'stock' => 120,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/plastic-containers/white-500ml/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/plastic-containers/white-500ml/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'material' => 'PET',
                            'lid_type' => 'screw-cap'
                        ]
                    ],
                    [
                        'id' => '10',
                        'variantId' => 'plastic-containers-blue-default-12',
                        'color' => 'Blue',
                        'colorHex' => '#3B82F6',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 12,
                        'packSizeId' => 12,
                        'sku' => 'PC-BLU-1L-12',
                        'price' => 89.99,
                        'salePrice' => null,
                        'inStock' => true,
                        'stock' => 85,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/plastic-containers/blue-1l/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/plastic-containers/blue-1l/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'material' => 'HDPE',
                            'lid_type' => 'snap-on'
                        ]
                    ],
                    [
                        'id' => '11',
                        'variantId' => 'plastic-containers-clear-default-6',
                        'color' => 'Clear',
                        'colorHex' => '#F3F4F6',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 6,
                        'packSizeId' => 6,
                        'sku' => 'PC-CLR-2L-6',
                        'price' => 124.99,
                        'salePrice' => 99.99,
                        'inStock' => true,
                        'stock' => 60,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/plastic-containers/clear-2l/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/plastic-containers/clear-2l/1.jpg'
                            ],
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/plastic-containers/clear-2l/2.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/plastic-containers/clear-2l/2.jpg'
                            ]
                        ],
                        'attributes' => [
                            'material' => 'Tritan',
                            'lid_type' => 'hinged'
                        ]
                    ]
                ],
                'parent' => 'Bottles & Containers',
                'tag' => 'Plastic Containers',
                'createdAt' => '2026-02-10T11:04:36Z',
                'updatedAt' => '2026-02-10T11:04:36Z'
            ],
            [
                'id' => 2,
                'name' => 'Stretch Film',
                'slug' => 'stretch-film',
                'baseDescription' => [
                    'en' => 'Durable stretch film for pallet wrapping and load securing',
                    'he' => 'סרטיש נמתיח לעטיפת פלטים ואבטחת מטענים'
                ],
                'category' => [
                    'id' => 1,
                    'categoryName' => 'Flexible Packaging',
                    'description' => 'Versatile packaging solutions for various product types',
                    'parent' => null,
                    'tag' => 'Bubble Wrap'
                ],
                'rating' => 4.2,
                'soldCount' => 892,
                'price' => 89.99,
                'salePrice' => 74.99,
                'options' => [
                    'colors' => [
                        [
                            'id' => 'black',
                            'name' => 'Black',
                            'value' => 'black',
                            'hex' => '#000000'
                        ],
                        [
                            'id' => 'clear',
                            'name' => 'Clear',
                            'value' => 'clear',
                            'hex' => '#F3F4F6'
                        ]
                    ],
                    'sizes' => [],
                    'pcsPerPack' => [
                        [
                            'id' => '1',
                            'name' => '1 roll',
                            'value' => 1,
                            'label' => '1 roll'
                        ]
                    ]
                ],
                'variants' => [
                    [
                        'id' => '4',
                        'variantId' => 'stretch-film-black-default-1',
                        'color' => 'Black',
                        'colorHex' => '#000000',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 1,
                        'packSizeId' => 1,
                        'sku' => 'SF-BLK-1',
                        'price' => 89.99,
                        'salePrice' => 74.99,
                        'inStock' => true,
                        'stock' => 45,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/stretch-film/black/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/stretch-film/black/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'gauge' => '80 gauge',
                            'length' => '1500 ft'
                        ]
                    ],
                    [
                        'id' => '5',
                        'variantId' => 'stretch-film-clear-default-1',
                        'color' => 'Clear',
                        'colorHex' => '#F3F4F6',
                        'size' => null,
                        'sizeId' => null,
                        'packSize' => 1,
                        'packSizeId' => 1,
                        'sku' => 'SF-CLR-1',
                        'price' => 94.99,
                        'salePrice' => null,
                        'inStock' => true,
                        'stock' => 62,
                        'images' => [
                            [
                                'thumbnail' => 'http://localhost:8000/storage/images/products/stretch-film/clear/1.jpg',
                                'large' => 'http://localhost:8000/storage/images/products/stretch-film/clear/1.jpg'
                            ]
                        ],
                        'attributes' => [
                            'gauge' => '90 gauge',
                            'length' => '1500 ft'
                        ]
                    ]
                ],
                'parent' => 'Flexible Packaging',
                'tag' => 'Bubble Wrap',
                'createdAt' => '2026-02-10T11:04:36Z',
                'updatedAt' => '2026-02-10T11:04:36Z'
            ]
        ];

        // Get sample images
        $sampleImages = glob(database_path('seeders/assets/product_*.jpg'));
        
        foreach ($products as $index => $productData) {
            // Create product
            $product = Product::create([
                'title' => $productData['name'],
                'slug' => $productData['slug'],
                'description' => $productData['baseDescription']['en'] ?? '',
                'price' => $productData['price'],
                'sale_price' => $productData['salePrice'] ?? null,
                'sku' => $productData['variants'][0]['sku'] ?? 'PROD-' . $productData['id'],
                'stock_qty' => array_sum(array_column($productData['variants'], 'stock')),
                'track_inventory' => true,
                'sort_order' => $productData['id'],
                'is_active' => true,
                'is_featured' => $index < 2, // First 2 products are featured
                'meta_title' => $productData['name'],
                'meta_description' => Str::limit($productData['baseDescription']['en'] ?? '', 160),
                'pieces_per_package' => $productData['variants'][0]['packSize'] ?? 1,
            ]);

            // Attach to category if exists
            $category = Category::where('name', $productData['category']['categoryName'])->first();
            if ($category) {
                $product->categories()->attach($category->id);
            }

            // Create sizes from pcsPerPack options
            if (!empty($productData['options']['pcsPerPack'])) {
                foreach ($productData['options']['pcsPerPack'] as $sizeIndex => $packSize) {
                    ProductSize::create([
                        'product_id' => $product->id,
                        'value' => $packSize['label'],
                        'sort_order' => $sizeIndex,
                    ]);
                }
            }

            // Create colors from options
            if (!empty($productData['options']['colors'])) {
                foreach ($productData['options']['colors'] as $colorIndex => $colorData) {
                    ProductColor::create([
                        'product_id' => $product->id,
                        'name' => $colorData['name'],
                        'hex' => $colorData['hex'],
                        'sort_order' => $colorIndex,
                    ]);
                }
            }

            // Create product images - use first variant's images or generate random count
            $imageCount = 1; // Default image count
            if (!empty($productData['variants'][0]['images'])) {
                $imageCount = count($productData['variants'][0]['images']);
            }
            
            $this->createProductImages($product, $imageCount, $sampleImages);

            $this->command->info("✓ Created product: {$product->title} with {$imageCount} images");
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