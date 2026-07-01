<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/config')) {
            return $next($request);
        }

        try {
            $settings = SystemSetting::set();

            if ($settings->is_maintenance_mode) {
                return response()->json([
                    'success' => false,
                    'message' => __('The system is currently under maintenance. Please try again later.'),
                ], 503);
            }
        } catch (\Exception) {
            // Fallback if database is not available
        }

        return $next($request);
    }
}
