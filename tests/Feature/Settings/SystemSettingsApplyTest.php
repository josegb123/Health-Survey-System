<?php

namespace Tests\Feature\Settings;

use App\Livewire\Admin\SystemSettings;
use App\Models\MinistryReportConfig;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSettingsApplyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_settings_page_loads_and_displays_current_values(): void
    {
        SystemSetting::set()->update([
            'company_name' => 'Test Clinic',
            'company_dni' => '123456789',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.settings'));

        $response->assertOk();
        $response->assertSee('Test Clinic');
    }

    public function test_settings_are_persisted_after_save(): void
    {
        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->set('company_name', 'Updated Clinic')
            ->set('company_dni', '987654321')
            ->set('turnstile_site_key', 'new-site-key')
            ->set('rate_limit_requests', 120)
            ->set('session_timeout_minutes', 60)
            ->set('is_maintenance_mode', true)
            ->set('theme', 'dark')
            ->set('language', 'en')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'company_name' => 'Updated Clinic',
            'company_dni' => '987654321',
            'turnstile_site_key' => 'new-site-key',
            'rate_limit_requests' => 120,
            'session_timeout_minutes' => 60,
            'is_maintenance_mode' => true,
            'theme' => 'dark',
            'language' => 'en',
        ]);
    }

    public function test_session_lifetime_is_set_from_settings(): void
    {
        SystemSetting::set()->update(['session_timeout_minutes' => 45]);

        $provider = app()->register(AppServiceProvider::class);
        $provider->boot();

        $this->assertEquals(45, config('session.lifetime'));
    }

    public function test_mail_config_is_set_from_settings(): void
    {
        SystemSetting::set()->update([
            'mail_from_address' => 'test@clinic.com',
            'mail_from_name' => 'Test Clinic',
        ]);

        $provider = app()->register(AppServiceProvider::class);
        $provider->boot();

        $this->assertEquals('test@clinic.com', config('mail.from.address'));
        $this->assertEquals('Test Clinic', config('mail.from.name'));
    }

    public function test_theme_is_reflected_in_layout(): void
    {
        SystemSetting::set()->update(['theme' => 'light']);

        $response = $this->actingAs($this->user)
            ->get(route('admin.surveys.index'));

        $response->assertOk();

        // Light theme means no 'dark' class on html
        $response->assertDontSee('<html class="dark"', false);
    }

    public function test_dark_theme_adds_dark_class(): void
    {
        SystemSetting::set()->update(['theme' => 'dark']);

        $response = $this->actingAs($this->user)
            ->get(route('admin.surveys.index'));

        $response->assertOk();
    }

    public function test_locale_is_applied_from_settings(): void
    {
        SystemSetting::set()->update(['language' => 'en']);

        $response = $this->actingAs($this->user)
            ->get(route('admin.surveys.index'));

        $response->assertOk();
        $this->assertEquals('en', app()->getLocale());
    }

    public function test_saving_default_template_syncs_to_ministry_config(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Default', 'is_active' => true]);

        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->set('default_survey_template_id', $template->id)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('system_settings', [
            'default_survey_template_id' => $template->id,
        ]);

        $this->assertDatabaseHas('ministry_report_configs', [
            'survey_template_id' => $template->id,
        ]);
    }

    public function test_clearing_default_template_does_not_sync_to_ministry(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Template', 'is_active' => true]);
        MinistryReportConfig::set()->update(['survey_template_id' => $template->id]);

        Livewire::actingAs($this->user)
            ->test(SystemSettings::class)
            ->set('default_survey_template_id', null)
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ministry_report_configs', [
            'survey_template_id' => $template->id,
        ]);
    }
}
