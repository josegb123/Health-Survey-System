<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlockIpAddress
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $blockedIps = Cache::remember('blocked_ips_list', 3600, function () {
            return config('security.blocked_ips', []);
        });
        if (in_array($clientIp, $blockedIps, true)) {
            return response()->json([
                'message' => 'Access denied. Your IP address has been blocked.',
            ], 403);
        }

        return $next($request);
    }
}
