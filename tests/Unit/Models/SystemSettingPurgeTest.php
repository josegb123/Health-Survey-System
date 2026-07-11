<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemSettingPurgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
    }

    public function test_purge_removes_surveys_older_than_6_months(): void
    {
        Storage::fake('local');

        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $question = SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'field_type' => 'text',
        ]);
        $patient = Patient::factory()->create();
        $signaturePath = 'signatures/test_old.png';
        Storage::disk('local')->put($signaturePath, 'fake-content');

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'signature_path' => $signaturePath,
            'created_at' => now()->subMonths(7),
        ]);

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
        ]);

        $result = SystemSetting::purgeOldSurveys();

        $this->assertStringContainsString('1', $result);
        $this->assertEquals(1, Survey::count());
        $this->assertEquals(0, Survey::where('created_at', '<', now()->subMonths(6))->count());
        $this->assertFalse(Storage::disk('local')->exists($signaturePath));
    }

    public function test_purge_deletes_orphan_patients(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        $this->assertEquals(1, Patient::count());

        SystemSetting::purgeOldSurveys();

        $this->assertEquals(0, Patient::count());
    }

    public function test_purge_keeps_patients_with_remaining_surveys(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
        ]);

        SystemSetting::purgeOldSurveys();

        $this->assertEquals(1, Patient::count());
    }

    public function test_purge_removes_survey_answers(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $question = SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'field_type' => 'text',
        ]);
        $patient = Patient::factory()->create();

        $survey = Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        SurveyAnswer::create([
            'survey_id' => $survey->id,
            'survey_question_id' => $question->id,
            'answer_value' => 'test answer',
        ]);

        $this->assertEquals(1, SurveyAnswer::count());

        SystemSetting::purgeOldSurveys();

        $this->assertEquals(0, SurveyAnswer::count());
    }

    public function test_purge_only_affects_completed_surveys_older_than_6_months(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'draft',
            'created_at' => now()->subMonths(7),
        ]);

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        SystemSetting::purgeOldSurveys();

        $this->assertEquals(1, Survey::count());
        $this->assertEquals('draft', Survey::first()->status);
    }

    public function test_purge_updates_timestamp(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonths(7),
        ]);

        SystemSetting::purgeOldSurveys();

        $settings = SystemSetting::set();
        $this->assertNotNull($settings->surveys_purge_last_run);
    }

    public function test_purge_with_no_old_surveys_returns_appropriate_message(): void
    {
        app()->setLocale('en');

        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
        ]);

        $result = SystemSetting::purgeOldSurveys();

        $this->assertStringContainsString('No surveys', $result);
        $this->assertEquals(1, Survey::count());
    }

    public function test_purge_deletes_signature_files(): void
    {
        Storage::fake('local');

        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();
        $sigPath = 'signatures/to_delete.png';
        Storage::disk('local')->put($sigPath, 'fake');

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'signature_path' => $sigPath,
            'created_at' => now()->subMonths(7),
        ]);

        $this->assertTrue(Storage::disk('local')->exists($sigPath));

        SystemSetting::purgeOldSurveys();

        $this->assertFalse(Storage::disk('local')->exists($sigPath));
    }
}
