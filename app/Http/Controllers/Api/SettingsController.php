<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService)
    {
        // Public endpoint - no authentication required
    }

    public function index(): JsonResponse
    {
        $settings = $this->settingsService->getPublicSettings();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }
}