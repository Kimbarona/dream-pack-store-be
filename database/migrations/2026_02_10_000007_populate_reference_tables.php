<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert common pack options
        DB::table('pack_options')->insert([
            [
                'value' => 1,
                'label' => '1 Pack',
                'slug' => '1-pack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 2,
                'label' => '2 Pack',
                'slug' => '2-pack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 3,
                'label' => '3 Pack',
                'slug' => '3-pack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 5,
                'label' => '5 Pack',
                'slug' => '5-pack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'value' => 10,
                'label' => '10 Pack',
                'slug' => '10-pack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert common sizes
        DB::table('sizes')->insert([
            ['name' => 'XS', 'slug' => 'xs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S', 'slug' => 's', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M', 'slug' => 'm', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'L', 'slug' => 'l', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'XL', 'slug' => 'xl', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'XXL', 'slug' => 'xxl', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3XL', 'slug' => '3xl', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert common colors from the existing product_colors
        $commonColors = [
            ['name' => 'Red', 'slug' => 'red', 'hex' => '#FF0000'],
            ['name' => 'Blue', 'slug' => 'blue', 'hex' => '#0000FF'],
            ['name' => 'Green', 'slug' => 'green', 'hex' => '#008000'],
            ['name' => 'Yellow', 'slug' => 'yellow', 'hex' => '#FFFF00'],
            ['name' => 'Black', 'slug' => 'black', 'hex' => '#000000'],
            ['name' => 'White', 'slug' => 'white', 'hex' => '#FFFFFF'],
            ['name' => 'Gray', 'slug' => 'gray', 'hex' => '#808080'],
            ['name' => 'Pink', 'slug' => 'pink', 'hex' => '#FFC0CB'],
            ['name' => 'Orange', 'slug' => 'orange', 'hex' => '#FFA500'],
            ['name' => 'Purple', 'slug' => 'purple', 'hex' => '#800080'],
        ];

        foreach ($commonColors as $color) {
            $color['created_at'] = now();
            $color['updated_at'] = now();
            DB::table('colors')->insert($color);
        }
    }

    public function down(): void
    {
        DB::table('colors')->truncate();
        DB::table('sizes')->truncate();
        DB::table('pack_options')->truncate();
    }
};