<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->where('is_active', true)->get();
        
        if ($products->isEmpty()) {
            $this->command->warn('⚠️ No products found. Please run CatalogSeeder first.');
            return;
        }

        $colors = DB::table('colors')->pluck('id', 'slug');
        $sizes = DB::table('sizes')->pluck('id', 'slug');
        $packOptions = DB::table('pack_options')->pluck('id', 'value');

        $variantTemplates = [
            'Stand-up Pouch' => [
                ['color' => 'red', 'size' => 'small', 'pack' => 6, 'price' => 24.99, 'stock' => 100],
                ['color' => 'red', 'size' => 'medium', 'pack' => 6, 'price' => 29.99, 'stock' => 150],
                ['color' => 'red', 'size' => 'large', 'pack' => 12, 'price' => 49.99, 'stock' => 80],
                ['color' => 'blue', 'size' => 'small', 'pack' => 6, 'price' => 24.99, 'stock' => 100],
                ['color' => 'blue', 'size' => 'medium', 'pack' => 6, 'price' => 29.99, 'stock' => 120],
                ['color' => 'blue', 'size' => 'large', 'pack' => 12, 'price' => 49.99, 'stock' => 75],
                ['color' => 'green', 'size' => 'small', 'pack' => 6, 'price' => 24.99, 'stock' => 90],
                ['color' => 'green', 'size' => 'medium', 'pack' => 6, 'price' => 29.99, 'stock' => 110],
                ['color' => 'black', 'size' => 'small', 'pack' => 6, 'price' => 26.99, 'stock' => 85],
                ['color' => 'black', 'size' => 'medium', 'pack' => 6, 'price' => 31.99, 'stock' => 95],
                ['color' => 'clear', 'size' => 'small', 'pack' => 6, 'price' => 22.99, 'stock' => 200],
                ['color' => 'clear', 'size' => 'medium', 'pack' => 12, 'price' => 39.99, 'stock' => 180],
            ],
            'Flow Wrap Film' => [
                ['color' => 'clear', 'size' => '50um', 'pack' => 1, 'price' => 89.99, 'stock' => 50],
                ['color' => 'clear', 'size' => '75um', 'pack' => 1, 'price' => 119.99, 'stock' => 35],
                ['color' => 'black', 'size' => '50um', 'pack' => 1, 'price' => 94.99, 'stock' => 25],
                ['color' => 'black', 'size' => '75um', 'pack' => 1, 'price' => 124.99, 'stock' => 20],
                ['color' => 'white', 'size' => '50um', 'pack' => 1, 'price' => 99.99, 'stock' => 30],
            ],
            'Corrugated Box' => [
                ['color' => 'brown', 'size' => 'small-30x20x20', 'pack' => 10, 'price' => 89.99, 'stock' => 200],
                ['color' => 'brown', 'size' => 'medium-40x30x30', 'pack' => 10, 'price' => 129.99, 'stock' => 150],
                ['color' => 'brown', 'size' => 'large-50x40x40', 'pack' => 10, 'price' => 189.99, 'stock' => 100],
                ['color' => 'white', 'size' => 'small-30x20x20', 'pack' => 10, 'price' => 99.99, 'stock' => 120],
                ['color' => 'white', 'size' => 'medium-40x30x30', 'pack' => 10, 'price' => 144.99, 'stock' => 80],
                ['color' => 'white', 'size' => 'large-50x40x40', 'pack' => 10, 'price' => 209.99, 'stock' => 50],
            ],
            'Rigid Gift Box' => [
                ['color' => 'red', 'size' => 'small', 'pack' => 6, 'price' => 44.99, 'stock' => 80],
                ['color' => 'red', 'size' => 'medium', 'pack' => 6, 'price' => 59.99, 'stock' => 60],
                ['color' => 'red', 'size' => 'large', 'pack' => 6, 'price' => 79.99, 'stock' => 40],
                ['color' => 'blue', 'size' => 'small', 'pack' => 6, 'price' => 44.99, 'stock' => 75],
                ['color' => 'blue', 'size' => 'medium', 'pack' => 6, 'price' => 59.99, 'stock' => 55],
                ['color' => 'blue', 'size' => 'large', 'pack' => 6, 'price' => 79.99, 'stock' => 35],
                ['color' => 'green', 'size' => 'small', 'pack' => 6, 'price' => 44.99, 'stock' => 70],
                ['color' => 'green', 'size' => 'medium', 'pack' => 6, 'price' => 59.99, 'stock' => 50],
                ['color' => 'black', 'size' => 'small', 'pack' => 6, 'price' => 49.99, 'stock' => 65],
                ['color' => 'black', 'size' => 'medium', 'pack' => 6, 'price' => 64.99, 'stock' => 45],
                ['color' => 'black', 'size' => 'large', 'pack' => 6, 'price' => 84.99, 'stock' => 30],
            ],
            'Plastic Bottle' => [
                ['color' => 'blue', 'size' => '500ml', 'pack' => 12, 'price' => 34.99, 'stock' => 150],
                ['color' => 'blue', 'size' => '1l', 'pack' => 12, 'price' => 44.99, 'stock' => 120],
                ['color' => 'blue', 'size' => '2l', 'pack' => 6, 'price' => 39.99, 'stock' => 100],
                ['color' => 'clear', 'size' => '500ml', 'pack' => 12, 'price' => 32.99, 'stock' => 180],
                ['color' => 'clear', 'size' => '1l', 'pack' => 12, 'price' => 42.99, 'stock' => 140],
                ['color' => 'clear', 'size' => '2l', 'pack' => 6, 'price' => 37.99, 'stock' => 110],
                ['color' => 'white', 'size' => '500ml', 'pack' => 12, 'price' => 36.99, 'stock' => 90],
                ['color' => 'white', 'size' => '1l', 'pack' => 12, 'price' => 46.99, 'stock' => 70],
            ],
            'Glass Jar' => [
                ['color' => 'clear', 'size' => 'small', 'pack' => 6, 'price' => 54.99, 'stock' => 60],
                ['color' => 'clear', 'size' => 'medium', 'pack' => 6, 'price' => 69.99, 'stock' => 45],
                ['color' => 'clear', 'size' => 'large', 'pack' => 6, 'price' => 89.99, 'stock' => 30],
                ['color' => 'green', 'size' => 'small', 'pack' => 6, 'price' => 59.99, 'stock' => 50],
                ['color' => 'green', 'size' => 'medium', 'pack' => 6, 'price' => 74.99, 'stock' => 40],
                ['color' => 'green', 'size' => 'large', 'pack' => 6, 'price' => 94.99, 'stock' => 25],
            ],
            'Bubble Wrap Roll' => [
                ['color' => 'clear', 'size' => 'small', 'pack' => 1, 'price' => 29.99, 'stock' => 200],
                ['color' => 'clear', 'size' => 'medium', 'pack' => 1, 'price' => 49.99, 'stock' => 150],
                ['color' => 'clear', 'size' => 'large', 'pack' => 1, 'price' => 79.99, 'stock' => 100],
                ['color' => 'blue', 'size' => 'small', 'pack' => 1, 'price' => 34.99, 'stock' => 80],
                ['color' => 'blue', 'size' => 'medium', 'pack' => 1, 'price' => 54.99, 'stock' => 60],
                ['color' => 'blue', 'size' => 'large', 'pack' => 1, 'price' => 84.99, 'stock' => 40],
            ],
            'Foam Peanuts' => [
                ['color' => 'white', 'size' => 'small', 'pack' => 1, 'price' => 39.99, 'stock' => 100],
                ['color' => 'white', 'size' => 'medium', 'pack' => 1, 'price' => 59.99, 'stock' => 80],
                ['color' => 'white', 'size' => 'large', 'pack' => 1, 'price' => 89.99, 'stock' => 50],
            ],
            'Shelf Display Box' => [
                ['color' => 'brown', 'size' => 'small', 'pack' => 10, 'price' => 69.99, 'stock' => 120],
                ['color' => 'brown', 'size' => 'medium', 'pack' => 10, 'price' => 99.99, 'stock' => 80],
                ['color' => 'brown', 'size' => 'large', 'pack' => 10, 'price' => 139.99, 'stock' => 50],
                ['color' => 'white', 'size' => 'small', 'pack' => 10, 'price' => 79.99, 'stock' => 90],
                ['color' => 'white', 'size' => 'medium', 'pack' => 10, 'price' => 109.99, 'stock' => 60],
                ['color' => 'white', 'size' => 'large', 'pack' => 10, 'price' => 149.99, 'stock' => 40],
            ],
            'Blister Pack' => [
                ['color' => 'clear', 'size' => 'small', 'pack' => 24, 'price' => 44.99, 'stock' => 150],
                ['color' => 'clear', 'size' => 'medium', 'pack' => 24, 'price' => 64.99, 'stock' => 100],
                ['color' => 'clear', 'size' => 'large', 'pack' => 24, 'price' => 89.99, 'stock' => 70],
                ['color' => 'blue', 'size' => 'small', 'pack' => 24, 'price' => 49.99, 'stock' => 80],
                ['color' => 'blue', 'size' => 'medium', 'pack' => 24, 'price' => 69.99, 'stock' => 60],
                ['color' => 'blue', 'size' => 'large', 'pack' => 24, 'price' => 94.99, 'stock' => 40],
            ],
        ];

        $totalVariantsCreated = 0;

        foreach ($products as $product) {
            $productName = $product->title;
            
            if (!isset($variantTemplates[$productName])) {
                $this->command->warn("⚠️ No variant template found for: $productName");
                continue;
            }

            $variants = $variantTemplates[$productName];
            $skuPrefix = strtoupper(Str::slug($productName, '-', null));
            $skuPrefix = substr($skuPrefix, 0, 6);

            foreach ($variants as $index => $variant) {
                $colorSlug = strtolower($variant['color']);
                $sizeSlug = strtolower($variant['size']);
                $packValue = $variant['pack'];

                if (!isset($colors[$colorSlug])) {
                    $this->command->warn("⚠️ Color not found: $colorSlug");
                    continue;
                }
                if (!isset($sizes[$sizeSlug])) {
                    $sizeId = null;
                } else {
                    $sizeId = $sizes[$sizeSlug];
                }
                if (!isset($packOptions[$packValue])) {
                    $this->command->warn("⚠️ Pack option not found: $packValue");
                    continue;
                }

                $sku = $skuPrefix . '-' . strtoupper(substr($colorSlug, 0, 3)) . '-' . ($index + 1);

                DB::table('product_variants')->insert([
                    'product_id' => $product->id,
                    'color_id' => $colors[$colorSlug],
                    'size_id' => $sizeId,
                    'pack_option_id' => $packOptions[$packValue],
                    'sku' => $sku,
                    'price' => $variant['price'],
                    'sale_price' => null,
                    'stock_qty' => $variant['stock'],
                    'attributes' => json_encode(['generated' => true]),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalVariantsCreated++;
            }

            $this->command->info("✅ Created " . count($variants) . " variants for $productName");
        }

        $this->command->info("🎉 Total variants created: $totalVariantsCreated");
    }
}
