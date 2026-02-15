<?php

namespace App\Http\Controllers\Api;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BannerController extends Controller
{
    /**
     * Get active banners for frontend display
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $banners = Banner::displayed()
            ->take($limit)
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'name' => $banner->name,
                    'title' => $banner->title,
                    'subtitle' => $banner->subtitle,
                    'link_url' => $banner->link_url,
                    'image' => $banner->image,
                    'image_url' => $banner->image_url,
                    'sort_order' => $banner->sort_order,
                ];
            });

        return response()->json($banners);
    }

    /**
     * Get banner by ID for preview
     */
    public function show(Banner $banner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $banner->id,
                'name' => $banner->name,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'link_url' => $banner->link_url,
                'image' => $banner->image,
                'image_url' => $banner->image_url,
                'is_active' => $banner->is_active,
                'sort_order' => $banner->sort_order,
                'starts_at' => $banner->starts_at,
                'ends_at' => $banner->ends_at,
                'created_at' => $banner->created_at,
            ],
        ]);
    }
}