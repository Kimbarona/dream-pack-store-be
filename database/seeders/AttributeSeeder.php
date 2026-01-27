<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attribute;
use App\Models\AttributeValue;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $sizeAttribute = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'description' => 'Product size options',
        ]);

        $sizeValues = [
            ['value' => 'Small', 'slug' => 'small', 'sort_order' => 1],
            ['value' => 'Medium', 'slug' => 'medium', 'sort_order' => 2],
            ['value' => 'Large', 'slug' => 'large', 'sort_order' => 3],
            ['value' => 'X-Large', 'slug' => 'x-large', 'sort_order' => 4],
            ['value' => 'XX-Large', 'slug' => 'xx-large', 'sort_order' => 5],
        ];

        foreach ($sizeValues as $sizeValue) {
            AttributeValue::create([
                'attribute_id' => $sizeAttribute->id,
                'value' => $sizeValue['value'],
                'slug' => $sizeValue['slug'],
                'sort_order' => $sizeValue['sort_order'],
            ]);
        }

        $colorAttribute = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'description' => 'Product color options',
        ]);

        $colorValues = [
            ['value' => 'Red', 'slug' => 'red', 'sort_order' => 1],
            ['value' => 'Blue', 'slug' => 'blue', 'sort_order' => 2],
            ['value' => 'Green', 'slug' => 'green', 'sort_order' => 3],
            ['value' => 'Black', 'slug' => 'black', 'sort_order' => 4],
            ['value' => 'White', 'slug' => 'white', 'sort_order' => 5],
            ['value' => 'Yellow', 'slug' => 'yellow', 'sort_order' => 6],
            ['value' => 'Purple', 'slug' => 'purple', 'sort_order' => 7],
            ['value' => 'Orange', 'slug' => 'orange', 'sort_order' => 8],
        ];

        foreach ($colorValues as $colorValue) {
            AttributeValue::create([
                'attribute_id' => $colorAttribute->id,
                'value' => $colorValue['value'],
                'slug' => $colorValue['slug'],
                'sort_order' => $colorValue['sort_order'],
            ]);
        }
    }
}