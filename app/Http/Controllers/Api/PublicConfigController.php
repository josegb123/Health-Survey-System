<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class PublicConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = SystemSetting::set();

        return response()->json([
            'success' => true,
            'data' => [
                'turnstile_site_key' => $settings->turnstile_site_key,
                'is_maintenance_mode' => (bool) $settings->is_maintenance_mode,
            ],
        ]);
    }
}
