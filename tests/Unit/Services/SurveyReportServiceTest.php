<?php

namespace Tests\Unit\Services;

use App\Models\Insurer;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Services\SurveyReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SurveyReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set();

        $this->service = app(SurveyReportService::class);
    }

    public function test_get_surveys_in_range_returns_only_completed_surveys(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subDays(5),
        ]);

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'draft',
            'created_at' => now()->subDays(3),
        ]);

        $start = now()->subDays(10)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $surveys = $this->service->getSurveysInRange($start, $end);

        $this->assertCount(1, $surveys);
        $this->assertEquals('completed', $surveys->first()->status);
    }

    public function test_get_surveys_in_range_respects_date_bounds(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'created_at' => now()->subDays(20),
        ]);

        $start = now()->subDays(10)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $surveys = $this->service->getSurveysInRange($start, $end);

        $this->assertCount(0, $surveys);
    }

    public function test_generate_surveys_report_returns_pdf_object(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Test Template', 'is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->count(3)->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $pdf = $this->service->generateSurveysReport($start, $end, 'monthly');

        $this->assertStringContainsString('%PDF', $pdf->output());
    }

    public function test_generate_statistics_report_returns_pdf_object(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Stats Template', 'is_active' => true]);
        $patient = Patient::factory()->create();
        $insurer = Insurer::factory()->create();
        $patient->insurer()->associate($insurer)->save();

        Survey::factory()->count(5)->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => 4.5,
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $pdf = $this->service->generateStatisticsReport($start, $end, 'monthly');

        $output = $pdf->output();
        $this->assertStringContainsString('%PDF', $output);
    }

    public function test_generate_ministry_report_returns_formatted_text(): void
    {
        $template = SurveyTemplate::factory()->create(['title' => 'Ministry Template', 'is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => 4.0,
            'created_at' => now()->subDay(),
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $this->assertStringContainsString('MINISTERIO DE SALUD', strtoupper($content));
        $this->assertStringContainsString('ENCUESTA #1', strtoupper($content));
        $this->assertStringContainsString('FIN DEL REPORTE', strtoupper($content));
    }

    public function test_generate_ministry_report_handles_empty_data(): void
    {
        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->subDay()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $this->assertStringContainsString('No se encontraron encuestas', $content);
    }
}
