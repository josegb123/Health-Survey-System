<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetAppLocale;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SetAppLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_locale_from_settings(): void
    {
        SystemSetting::set()->update(['language' => 'en']);

        $middleware = new SetAppLocale();
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals('en', app()->getLocale());
    }

    public function test_defaults_to_spanish_when_no_language_set(): void
    {
        SystemSetting::set()->update(['language' => '']);

        $middleware = new SetAppLocale();
        $request = Request::create('/test', 'GET');

        $middleware->handle($request, function () {
            return response('OK', 200);
        });

        $this->assertEquals('es', app()->getLocale());
    }
}
