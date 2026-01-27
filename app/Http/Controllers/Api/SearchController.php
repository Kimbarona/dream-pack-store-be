<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __construct()
    {
        // Public API - no authentication required
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $searchTerm = $request->q;
        $limit = $request->input('limit', 10);

        $products = Product::with(['categories', 'attributeValues.attribute', 'images'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%");
            })
            ->orderBy('title')
            ->take($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'search_term' => $searchTerm,
                'count' => $products->count(),
                'limit' => $limit,
            ],
        ]);
    }
}