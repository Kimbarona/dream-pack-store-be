<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductFilterRequest;
use App\Http\Resources\Api\ProductListResource;
use App\Http\Resources\Api\ProductDetailResource;
use App\Http\Resources\Api\ProductFrontendCollection;
use App\Http\Resources\Api\ProductFrontendResource;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    public function __construct()
    {
        // Public API - no authentication required
    }

    public function index(ProductFilterRequest $request)
    {
        // Lightweight query for list views with variants
        $query = Product::with([
                'category.parent',
                'categories',
                'variants.color',
                'variants.size', 
                'variants.packOption',
                'variants.images',
                'images' => function($q) {
                    $q->where('is_featured', true)->limit(1);
                }
            ])
            ->where('is_active', true)
            ->whereNull('deleted_at');

        // Category filter (include descendants)
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = $this->getCategoryIds($category);
                $query->whereHas('categories', function (Builder $q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        // Price range filter - check variant prices
        if ($request->has('price_min')) {
            $query->where(function (Builder $q) use ($request) {
                $q->whereHas('variants', function (Builder $variantQuery) use ($request) {
                    $variantQuery->where('price', '>=', (float) $request->price_min)->where('is_active', true);
                })->orWhere(function (Builder $productQuery) use ($request) {
                    $productQuery->where('price', '>=', (float) $request->price_min)
                                 ->whereDoesntHave('variants');
                });
            });
        }
        if ($request->has('price_max')) {
            $query->where(function (Builder $q) use ($request) {
                $q->whereHas('variants', function (Builder $variantQuery) use ($request) {
                    $variantQuery->where('price', '<=', (float) $request->price_max)->where('is_active', true);
                })->orWhere(function (Builder $productQuery) use ($request) {
                    $productQuery->where('price', '<=', (float) $request->price_max)
                                 ->whereDoesntHave('variants');
                });
            });
        }

        // Search filter
        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        // Stock filter - check variants or product stock
        if ($request->boolean('in_stock')) {
            $query->where(function (Builder $q) {
                $q->whereHas('variants', function (Builder $variantQuery) {
                    $variantQuery->where('stock_qty', '>', 0)->where('is_active', true);
                })
                ->orWhere(function (Builder $productQuery) {
                    $productQuery->where('stock_qty', '>', 0)
                                ->where('track_inventory', true)
                                ->whereDoesntHave('variants');
                })
                ->orWhere('track_inventory', false);
            });
        }

        // Attribute filters
        if ($request->has('attributes.size')) {
            $query->whereHas('attributeValues', function (Builder $q) use ($request) {
                $q->whereHas('attribute', function (Builder $subQ) {
                    $subQ->where('slug', 'size');
                })
                ->where('slug', $request->input('attributes.size'));
            });
        }

        if ($request->has('attributes.color')) {
            $colors = $request->input('attributes.color');
            $query->whereHas('attributeValues', function (Builder $q) use ($colors) {
                $q->whereHas('attribute', function (Builder $subQ) {
                    $subQ->where('slug', 'color');
                })
                ->whereIn('attribute_values.slug', $colors);
            });
        }

        // Sorting - handle variant pricing
        switch ($request->input('sort', 'manual')) {
            case 'price_asc':
                $query->orderBy(function (Builder $q) {
                    $q->selectRaw('MIN(CASE WHEN variants.price IS NOT NULL THEN variants.price ELSE products.price END)')
                      ->from('variants')
                      ->whereColumn('variants.product_id', 'products.id')
                      ->where('variants.is_active', true)
                      ->limit(1);
                })->orderBy('title', 'asc');
                break;
            case 'price_desc':
                $query->orderByDesc(function (Builder $q) {
                    $q->selectRaw('MIN(CASE WHEN variants.price IS NOT NULL THEN variants.price ELSE products.price END)')
                      ->from('variants')
                      ->whereColumn('variants.product_id', 'products.id')
                      ->where('variants.is_active', true)
                      ->limit(1);
                })->orderBy('title', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('sort_order', 'asc')->orderBy('title', 'asc');
                break;
        }

        $products = $query->paginate(
            $request->input('per_page', 12)
        );

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                'products' => ProductFrontendResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ]
            ]
]);

    public function featured(Request $request): JsonResponse
    {
        $query = Product::with([
                'category.parent',
                'categories',
                'variants.color',
                'variants.size', 
                'variants.packOption',
                'variants.images',
                'images' => function($q) {
                    $q->where('is_featured', true)->limit(1);
                }
            ])
            ->where('is_featured', true)
            ->where('is_active', true)
            ->whereNull('deleted_at');

        // Respect is_active query param if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by newest first, then by rating/sold_count for better featured products
        $query->orderBy('created_at', 'desc')
              ->orderBy('rating', 'desc')
              ->orderBy('sold_count', 'desc');

        $products = $query->paginate(
            $request->input('limit', $request->input('per_page', 12))
        );

        return response()->json([
            'success' => true,
            'message' => 'Featured products retrieved successfully',
            'data' => [
                'products' => ProductFrontendResource::collection($products->items()),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more' => $products->hasMorePages(),
                ]
            ]
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with([
                'category.parent',
                'categories',
                'variants.color',
                'variants.size', 
                'variants.packOption',
                'variants.images',
                'images',
                'attributeValues.attribute'
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductFrontendResource($product),
        ]);
    }

    private function getCategoryIds(Category $category): array
    {
        $ids = [$category->id];
        
        // Get all descendants recursively
        $children = Category::where('parent_id', $category->id)->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getCategoryIds($child));
        }
        
        return $ids;
    }
}