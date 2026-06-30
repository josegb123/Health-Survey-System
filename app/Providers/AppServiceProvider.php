<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
        $this->configureSession();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure API rate limiting from system settings.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $maxAttempts = 60;

            try {
                $settings = SystemSetting::set();
                $maxAttempts = (int) ($settings->rate_limit_requests ?? 60);
            } catch (\Exception) {
                // Fallback if database is not available
            }

            return Limit::perMinute($maxAttempts)
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure session timeout from system settings.
     */
    protected function configureSession(): void
    {
        try {
            $settings = SystemSetting::set();

            if (! empty($settings->session_timeout_minutes)) {
                config(['session.lifetime' => (int) $settings->session_timeout_minutes]);
            }
        } catch (\Exception) {
            // Fallback if database is not available
        }
    }
}
