<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, migrate existing product data to base_description
        DB::statement('UPDATE products SET base_description = jsonb_build_object(\'en\', description) WHERE base_description IS NULL');
        
        // Create default variants for existing products that don't have variants
        $existingProducts = DB::table('products')->select('id', 'sku', 'price', 'sale_price', 'stock_qty', 'pieces_per_package', 'slug')->get();
        
        foreach ($existingProducts as $product) {
            // Check if product already has variants
            $existingVariant = DB::table('product_variants')->where('product_id', $product->id)->first();
            
            if (!$existingVariant) {
                // Find pack option that matches the product's pieces_per_package
                $packOption = DB::table('pack_options')->where('value', $product->pieces_per_package ?? 1)->first();
                
                DB::table('product_variants')->insert([
                    'product_id' => $product->id,
                    'color_id' => null,
                    'size_id' => null,
                    'pack_option_id' => $packOption?->id,
                    'sku' => $product->sku ?: ($product->slug . '-default'),
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_qty' => $product->stock_qty,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // Copy product images to variant images for default variants
        $productImages = DB::table('product_images')->get();
        
        foreach ($productImages as $productImage) {
            $defaultVariant = DB::table('product_variants')
                ->where('product_id', $productImage->product_id)
                ->where(function ($query) {
                    $query->whereNull('color_id')
                          ->whereNull('size_id')
                          ->whereNull('pack_option_id');
                })
                ->first();
                
            if ($defaultVariant) {
                // Check if image already exists for this variant
                $existingImage = DB::table('product_variant_images')
                    ->where('product_variant_id', $defaultVariant->id)
                    ->where('path', $productImage->path)
                    ->first();
                    
                if (!$existingImage) {
                    DB::table('product_variant_images')->insert([
                        'product_variant_id' => $defaultVariant->id,
                        'path' => $productImage->path,
                        'alt_text' => $productImage->alt_text,
                        'sort_order' => $productImage->sort_order,
                        'created_at' => $productImage->created_at,
                        'updated_at' => $productImage->updated_at,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // This is a complex migration to reverse, so we'll leave the data as is
        // In production, you would want to create proper down migration logic
    }
};