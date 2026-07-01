<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAppLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $settings = SystemSetting::set();
            $locale = $settings->language ?? 'es';

            if (empty($locale)) {
                $locale = 'es';
            }

            if (in_array($locale, ['es', 'en'])) {
                app()->setLocale($locale);
            }
        } catch (\Exception) {
            // Fallback if database is not available
        }

        return $next($request);
    }
}
