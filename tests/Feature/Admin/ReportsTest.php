<?php

namespace Tests\Feature\Admin;

use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
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

    public function test_surveys_page_loads(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.surveys.index'));

        $response->assertOk();
    }

    public function test_download_surveys_report_returns_pdf(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->count(2)->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->user);

        $component = app(\App\Livewire\Admin\SurveyIndex::class);
        $component->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $component->reportEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $response = $component->downloadSurveysReport();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_download_statistics_report_returns_pdf(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Stats Test', 'is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->count(3)->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => 4.0,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->user);

        $component = app(\App\Livewire\Admin\SurveyIndex::class);
        $component->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $component->reportEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $response = $component->downloadStatisticsReport();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_download_ministry_report_returns_txt(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Ministry', 'is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => 3.5,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->user);

        $component = app(\App\Livewire\Admin\SurveyIndex::class);
        $component->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $component->reportEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        $response = $component->downloadMinistryReport();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $response);
        $this->assertStringContainsString('.txt', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_report_date_validation_fails_with_invalid_dates(): void
    {
        $this->actingAs($this->user);

        $component = app(\App\Livewire\Admin\SurveyIndex::class);
        $component->reportStartDate = 'invalid-date';
        $component->reportEndDate = '2024-01-01';
        $component->reportPeriod = 'custom';

        try {
            $component->downloadSurveysReport();
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('reportStartDate', $e->errors());
        }
    }

    public function test_report_date_validation_fails_when_end_before_start(): void
    {
        $this->actingAs($this->user);

        $component = app(\App\Livewire\Admin\SurveyIndex::class);
        $component->reportStartDate = '2024-12-01';
        $component->reportEndDate = '2024-01-01';
        $component->reportPeriod = 'custom';

        try {
            $component->downloadSurveysReport();
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('reportEndDate', $e->errors());
        }
    }
}
