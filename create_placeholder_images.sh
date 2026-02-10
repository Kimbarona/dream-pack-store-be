#!/bin/bash

# Create placeholder image files for seeder
image_dirs=(
    "storage/app/public/images/products/bubble-wrap/blue"
    "storage/app/public/images/products/bubble-wrap/clear"
    "storage/app/public/images/products/stretch-film/black"
    "storage/app/public/images/products/stretch-film/clear"
    "storage/app/public/images/products/corrugated-boxes/brown-small"
    "storage/app/public/images/products/corrugated-boxes/brown-medium"
    "storage/app/public/images/products/corrugated-boxes/white-large"
    "storage/app/public/images/products/plastic-containers/white-500ml"
    "storage/app/public/images/products/plastic-containers/blue-1l"
    "storage/app/public/images/products/plastic-containers/clear-2l"
)

for dir in "${image_dirs[@]}"; do
    mkdir -p "$dir"
    echo "placeholder" > "$dir/1.jpg"
    echo "placeholder" > "$dir/2.jpg" 2>/dev/null || true
done

echo "✅ Created placeholder images for all directories"