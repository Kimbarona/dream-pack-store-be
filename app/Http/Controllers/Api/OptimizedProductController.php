<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductListResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cacheKey = 'products_' . md5(http_build_query($request->all()));
        
        $products = Cache::remember($cacheKey, 900, function () use ($request) {
            $query = Product::query()
                ->with(['images', 'categories'])
                ->where('is_active', true);

            // Filter by category
            if ($request->has('category_id')) {
                $categoryIds = $this->getCategoryIds(Category::findOrFail($request->category_id));
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                });
            }

            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Search
            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'ILIKE', '%' . $request->search . '%')
                      ->orWhere('description', 'ILIKE', '%' . $request->search . '%');
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            return $query->paginate($request->get('per_page', 12));
        });

        return response()->json([
            'success' => true,
            'data' => ProductListResource::collection($products),
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $cacheKey = "product_{$slug}";
        
        $product = Cache::remember($cacheKey, 3600, function () use ($slug) {
            return Product::with(['images', 'categories', 'attributeValues.attribute'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();
        });

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductListResource($product),
        ]);
    }

    private function getCategoryIds(Category $category): array
    {
        $cacheKey = "category_children_{$category->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($category) {
            $ids = [$category->id];
            
            // Get all descendants recursively (optimized to prevent deep recursion)
            $children = Category::where('parent_id', $category->id)->get(['id']);
            foreach ($children as $child) {
                $ids = array_merge($ids, $this->getCategoryIds($child));
            }
            
            return $ids;
        });
    }
}