<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\Product;

$app = require_once __DIR__ . '/bootstrap/app.php';

$product = Product::with(['variants.color', 'variants.size', 'variants.packOption', 'variants.images'])->first();

if ($product) {
    echo "Product: " . $product->title . "\n";
    echo "Variants: " . $product->variants->count() . "\n";
    
    foreach ($product->variants as $variant) {
        echo "- Variant SKU: " . $variant->sku . "\n";
        echo "  Color: " . ($variant->color?->name ?? 'None') . "\n";
        echo "  Size: " . ($variant->size?->name ?? 'None') . "\n";
        echo "  Pack: " . ($variant->packOption?->label ?? 'None') . "\n";
        echo "  Price: " . $variant->price . "\n";
        echo "  Stock: " . $variant->stock_qty . "\n";
        echo "  Images: " . $variant->images->count() . "\n";
        echo "\n";
    }
} else {
    echo "No product found\n";
}