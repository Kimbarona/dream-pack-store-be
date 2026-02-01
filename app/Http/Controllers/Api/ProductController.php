<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductFilterRequest;
use App\Http\Resources\Api\ProductListResource;
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
        // Lightweight query for list views
        $query = Product::with(['categories', 'images' => function($q) {
                $q->where('is_featured', true)->limit(1);
            }])
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

        // Price range filter
        if ($request->has('price_min')) {
            $query->where('price', '>=', (float) $request->price_min);
        }
        if ($request->has('price_max')) {
            $query->where('price', '<=', (float) $request->price_max);
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

        // Stock filter
        if ($request->boolean('in_stock')) {
            $query->where(function (Builder $q) {
                $q->where('stock_qty', '>', 0)
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

        // Sorting
        switch ($request->input('sort', 'manual')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
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

        return ProductListResource::collection($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['categories', 'attributeValues.attribute', 'images'])
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
            'data' => new ProductResource($product),
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