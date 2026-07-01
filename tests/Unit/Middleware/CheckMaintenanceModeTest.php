<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckMaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_request_when_maintenance_is_off(): void
    {
        SystemSetting::set()->update(['is_maintenance_mode' => false]);

        $middleware = new CheckMaintenanceMode();
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_request_when_maintenance_is_on(): void
    {
        SystemSetting::set()->update(['is_maintenance_mode' => true]);

        $middleware = new CheckMaintenanceMode();
        $request = Request::create('/api/test', 'GET');

        $response = $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals(503, $response->getStatusCode());
        $this->assertStringContainsString('maintenance', $response->getContent());
    }
}
