<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Apparel and clothing items',
                'sort_order' => 1,
                'meta_title' => 'Clothing - Dream Pack',
                'meta_description' => 'Browse our collection of quality clothing',
                'children' => [
                    [
                        'name' => 'Men',
                        'slug' => 'men',
                        'description' => 'Men\'s clothing',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'T-Shirts', 'slug' => 'men-t-shirts', 'sort_order' => 1],
                            ['name' => 'Pants', 'slug' => 'men-pants', 'sort_order' => 2],
                            ['name' => 'Jackets', 'slug' => 'men-jackets', 'sort_order' => 3],
                        ]
                    ],
                    [
                        'name' => 'Women',
                        'slug' => 'women',
                        'description' => 'Women\'s clothing',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Dresses', 'slug' => 'women-dresses', 'sort_order' => 1],
                            ['name' => 'Tops', 'slug' => 'women-tops', 'sort_order' => 2],
                            ['name' => 'Skirts', 'slug' => 'women-skirts', 'sort_order' => 3],
                        ]
                    ],
                    [
                        'name' => 'Kids',
                        'slug' => 'kids',
                        'description' => 'Kids clothing',
                        'sort_order' => 3,
                        'children' => [
                            ['name' => 'Boys', 'slug' => 'boys', 'sort_order' => 1],
                            ['name' => 'Girls', 'slug' => 'girls', 'sort_order' => 2],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Fashion accessories',
                'sort_order' => 2,
                'meta_title' => 'Accessories - Dream Pack',
                'meta_description' => 'Complete your look with our accessories',
                'children' => [
                    [
                        'name' => 'Bags',
                        'slug' => 'bags',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Backpacks', 'slug' => 'backpacks', 'sort_order' => 1],
                            ['name' => 'Handbags', 'slug' => 'handbags', 'sort_order' => 2],
                            ['name' => 'Wallets', 'slug' => 'wallets', 'sort_order' => 3],
                        ]
                    ],
                    [
                        'name' => 'Jewelry',
                        'slug' => 'jewelry',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Necklaces', 'slug' => 'necklaces', 'sort_order' => 1],
                            ['name' => 'Bracelets', 'slug' => 'bracelets', 'sort_order' => 2],
                            ['name' => 'Earrings', 'slug' => 'earrings', 'sort_order' => 3],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Home decor and lifestyle products',
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Bedroom',
                        'slug' => 'bedroom',
                        'sort_order' => 1,
                        'children' => [
                            ['name' => 'Bedding', 'slug' => 'bedding', 'sort_order' => 1],
                            ['name' => 'Pillows', 'slug' => 'pillows', 'sort_order' => 2],
                        ]
                    ],
                    [
                        'name' => 'Kitchen',
                        'slug' => 'kitchen',
                        'sort_order' => 2,
                        'children' => [
                            ['name' => 'Cookware', 'slug' => 'cookware', 'sort_order' => 1],
                            ['name' => 'Dinnerware', 'slug' => 'dinnerware', 'sort_order' => 2],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Sports and fitness equipment',
                'sort_order' => 5,
                'is_active' => true,
            ],
[
                'name' => 'General Clothing',
                'slug' => 'general-clothing',
                'description' => 'All clothing items',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Fashion Accessories',
                'slug' => 'fashion-accessories',
                'description' => 'Fashion accessories',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Fine Jewelry',
                'slug' => 'fine-jewelry',
                'description' => 'Jewelry items',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living-2',
                'description' => 'Home products',
                'sort_order' => 9,
                'is_active' => true,
            ]
        ];

        foreach ($categories as $categoryData) {
            $this->createCategoryWithChildren($categoryData);
        }
    }

    private function createCategoryWithChildren(array $data, int $parentId = null): Category
    {
        $category = Category::create([
            'parent_id' => $parentId,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'is_active' => true,
        ]);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->createCategoryWithChildren($childData, $category->id);
            }
        }

        return $category;
    }
}