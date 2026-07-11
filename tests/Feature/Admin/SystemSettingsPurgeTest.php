<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\SystemSettings;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSettingsPurgeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set()->update([
            'company_dni' => '4545454545',
            'entity_type' => 'NI',
            'registry_type' => 3,
        ]);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_settings_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.settings'));

        $response->assertOk();
    }

    public function test_start_purge_sets_step_to_1(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');

        $this->assertEquals(1, $component->get('purgeStep'));
    }

    public function test_next_purge_step_sets_step_to_2(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');
        $component->call('nextPurgeStep');

        $this->assertEquals(2, $component->get('purgeStep'));
    }

    public function test_cancel_purge_resets_step_to_0(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');
        $component->call('nextPurgeStep');
        $component->call('cancelPurge');

        $this->assertEquals(0, $component->get('purgeStep'));
        $this->assertEquals('', $component->get('purgeResult'));
        $this->assertEquals('', $component->get('confirmText'));
    }

    public function test_execute_purge_requires_correct_confirmation_text(): void
    {
        app()->setLocale('en');
        $this->actingAs($this->user);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');
        $component->call('nextPurgeStep');
        $component->set('confirmText', 'wrong text');
        $component->call('executePurge');

        $component->assertHasErrors('confirmText');
        $this->assertNotEquals(3, $component->get('purgeStep'));
    }

    public function test_execute_purge_with_correct_text_proceeds(): void
    {
        app()->setLocale('en');
        $this->actingAs($this->user);

        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();
        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');
        $component->call('nextPurgeStep');
        $component->set('confirmText', 'DELETE ALL');
        $component->call('executePurge');

        $this->assertEquals(3, $component->get('purgeStep'));
        $this->assertStringContainsString('1', $component->get('purgeResult'));
        $this->assertEquals(0, Survey::count());
    }

    public function test_execute_purge_with_correct_translated_text_proceeds(): void
    {
        app()->setLocale('es');
        $this->actingAs($this->user);

        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();
        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        $component = Livewire::test(SystemSettings::class);

        $component->call('startPurge');
        $component->call('nextPurgeStep');
        $component->set('confirmText', 'ELIMINAR TODO');
        $component->call('executePurge');

        $this->assertEquals(3, $component->get('purgeStep'));
        $this->assertStringContainsString('1', $component->get('purgeResult'));
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $component = Livewire::actingAs($user)->test(SystemSettings::class);

        $component->assertRedirect(route('dashboard'));
    }

    public function test_purge_displays_last_run_time(): void
    {
        $this->actingAs($this->user);

        SystemSetting::set()->update([
            'surveys_purge_last_run' => now()->subDay(),
        ]);

        $component = Livewire::test(SystemSettings::class);

        $this->assertNotNull($component->get('surveys_purge_last_run'));
    }
}
