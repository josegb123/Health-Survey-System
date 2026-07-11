<?php

namespace Tests\Unit\Services;

use App\Models\Insurer;
use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Services\SurveyReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SurveyReportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::set()->update([
            'company_dni' => '4545454545',
            'entity_type' => 'NI',
            'registry_type' => 3,
        ]);

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

    public function test_generate_ministry_report_uses_rating_for_experience(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => 5.0,
            'created_at' => now()->subDay(),
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $parts = explode('|', trim($content));
        // 1 MUY BUENA (rating 5.0), 0 BUENA, 0 REGULAR, 0 MALA, 0 MUY MALA
        $this->assertEquals(1, (int) $parts[4]);
        $this->assertEquals(0, (int) $parts[5]);
        $this->assertEquals(0, (int) $parts[6]);
        $this->assertEquals(0, (int) $parts[7]);
        $this->assertEquals(0, (int) $parts[8]);
        $this->assertEquals(0, (int) $parts[9]);
        // 1 DEFINITIVAMENTE SÍ (rating >= 3.5)
        $this->assertEquals(1, (int) $parts[10]);
        $this->assertEquals(0, (int) $parts[11]);
        $this->assertEquals(0, (int) $parts[12]);
        $this->assertEquals(0, (int) $parts[13]);
        $this->assertEquals(0, (int) $parts[14]);
    }

    public function test_generate_ministry_report_aggregates_all_surveys(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        $ratings = [5.0, 4.0, 3.0, 2.0, 1.0];
        foreach ($ratings as $i => $rating) {
            Survey::factory()->create([
                'survey_template_id' => $template->id,
                'patient_id' => $patient->id,
                'status' => 'completed',
                'rating' => $rating,
                'created_at' => now()->subDays($i),
            ]);
        }

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $parts = explode('|', trim($content));
        // exp: 5.0->MUY_BUENA, 4.0->BUENA, 3.0->REGULAR, 2.0->MALA, 1.0->MUY MALA
        $this->assertEquals(1, (int) $parts[4]); // MUY BUENA
        $this->assertEquals(1, (int) $parts[5]); // BUENA
        $this->assertEquals(1, (int) $parts[6]); // REGULAR
        $this->assertEquals(1, (int) $parts[7]); // MALA
        $this->assertEquals(1, (int) $parts[8]); // MUY MALA
        $this->assertEquals(0, (int) $parts[9]); // no responde
        // rec: ratings >= 3.5 -> DEFINITIVAMENTE SI (5.0, 4.0), others -> DEFINITIVAMENTE NO
        $this->assertEquals(2, (int) $parts[10]); // DEFINITIVAMENTE SÍ
        $this->assertEquals(0, (int) $parts[11]); // PROBABLEMENTE SÍ
        $this->assertEquals(3, (int) $parts[12]); // DEFINITIVAMENTE NO
        $this->assertEquals(0, (int) $parts[13]); // PROBABLEMENTE NO
        $this->assertEquals(0, (int) $parts[14]); // no responde
    }

    public function test_generate_ministry_report_maps_radio_answers(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        $expQuestion = SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'question_text' => '¿Cómo califica su experiencia global con la IPS?',
            'field_type' => 'radio',
            'options' => ['MUY BUENA', 'BUENA', 'REGULAR', 'MALA', 'MUY MALA'],
            'is_required' => true,
        ]);

        $recQuestion = SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'question_text' => '¿Recomendaría esta IPS a otras personas?',
            'field_type' => 'radio',
            'options' => ['DEFINITIVAMENTE SÍ', 'PROBABLEMENTE SÍ', 'DEFINITIVAMENTE NO', 'PROBABLEMENTE NO'],
            'is_required' => true,
        ]);

        $survey = Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => null,
            'created_at' => now()->subDay(),
        ]);

        SurveyAnswer::create([
            'survey_id' => $survey->id,
            'survey_question_id' => $expQuestion->id,
            'answer_value' => 'MUY BUENA',
        ]);
        SurveyAnswer::create([
            'survey_id' => $survey->id,
            'survey_question_id' => $recQuestion->id,
            'answer_value' => 'DEFINITIVAMENTE SÍ',
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $expected = '3|1|NI|4545454545|1|0|0|0|0|0|1|0|0|0|0';
        $this->assertEquals($expected, trim($content));
    }

    public function test_generate_ministry_report_maps_yes_no_answers_to_recommendation(): void
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        $patient = Patient::factory()->create();

        $yesNoQuestion = SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'question_text' => 'Was the staff courteous and professional?',
            'field_type' => 'radio',
            'options' => ['Yes', 'No'],
            'is_required' => true,
        ]);

        $survey = Survey::factory()->create([
            'survey_template_id' => $template->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'rating' => null,
            'created_at' => now()->subDay(),
        ]);

        SurveyAnswer::create([
            'survey_id' => $survey->id,
            'survey_question_id' => $yesNoQuestion->id,
            'answer_value' => 'Yes',
        ]);

        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $parts = explode('|', trim($content));
        // No experience answer
        $this->assertEquals(0, (int) $parts[4]);
        $this->assertEquals(1, (int) $parts[9]); // exp no responde
        // Yes -> DEFINITIVAMENTE SÍ
        $this->assertEquals(1, (int) $parts[10]);
        $this->assertEquals(0, (int) $parts[12]);
    }

    public function test_generate_ministry_report_handles_empty_data(): void
    {
        $start = now()->subMonth()->format('Y-m-d');
        $end = now()->subDay()->format('Y-m-d');

        $content = $this->service->generateMinistryReport($start, $end, 'monthly');

        $this->assertStringContainsString('No se encontraron encuestas', $content);
    }
}
