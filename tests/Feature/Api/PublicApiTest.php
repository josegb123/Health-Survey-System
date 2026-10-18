<?php

namespace Tests\Feature\Api;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::set();
    }

    protected function createTemplateWithQuestion(): SurveyTemplate
    {
        $template = SurveyTemplate::factory()->create(['is_active' => true]);
        SurveyQuestion::factory()->create([
            'survey_template_id' => $template->id,
            'question_text' => 'Test question',
            'field_type' => 'text',
            'is_required' => false,
        ]);

        return $template;
    }

    protected function validPayload(int $templateId, ?int $questionId = null): array
    {
        $questionId = $questionId ?? SurveyQuestion::where('survey_template_id', $templateId)->first()?->id
            ?? SurveyQuestion::factory()->create(['survey_template_id' => $templateId])->id;

        return [
            'patient' => [
                'name' => 'Test Patient',
                'dni' => '123456789',
                'document_type' => 'CC',
            ],
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'answers' => [
                ['question_id' => $questionId, 'value' => 'Test answer'],
            ],
            'cf_turnstile_token' => 'valid-token',
        ];
    }

    public function test_config_endpoint_returns_turnstile_key(): void
    {
        SystemSetting::set()->update([
            'turnstile_site_key' => '1x00000000000000000000AA',
            'is_maintenance_mode' => false,
        ]);

        $response = $this->getJson('/api/config');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'turnstile_site_key' => '1x00000000000000000000AA',
                    'is_maintenance_mode' => false,
                ],
            ]);
    }

    public function test_config_endpoint_reflects_maintenance_mode(): void
    {
        SystemSetting::set()->update(['is_maintenance_mode' => true]);

        $response = $this->getJson('/api/config');

        $response->assertOk()
            ->assertJsonPath('data.is_maintenance_mode', true);
    }

    public function test_api_returns_503_when_maintenance_mode_on(): void
    {
        SurveyTemplate::factory()->create(['id' => 1, 'is_active' => true]);
        SystemSetting::set()->update(['is_maintenance_mode' => true]);

        $response = $this->getJson('/api/survey-templates/1');

        $response->assertStatus(503);
    }

    public function test_survey_submission_rejects_missing_turnstile_token(): void
    {
        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        unset($payload['cf_turnstile_token']);

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cf_turnstile_token']);
    }

    public function test_survey_submission_validates_turnstile_token(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        SystemSetting::set()->update(['turnstile_secret_key' => 'test-secret']);

        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        $payload['cf_turnstile_token'] = 'valid-token';

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        // Should get past validation (may fail on processing due to missing data)
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    public function test_survey_submission_fails_with_invalid_turnstile(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        SystemSetting::set()->update(['turnstile_secret_key' => 'test-secret']);

        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        $payload['cf_turnstile_token'] = 'invalid-token';

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cf_turnstile_token']);
    }

    public function test_skips_turnstile_validation_when_no_secret_configured(): void
    {
        Http::fake();

        SystemSetting::set()->update(['turnstile_secret_key' => null]);

        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        $payload['cf_turnstile_token'] = 'some-token';

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        // Should pass validation (no Turnstile check) and fail on processing
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    /*
    |--------------------------------------------------------------------------
    | Tests Adicionales: Rate Limiting & Casos Borde
    |--------------------------------------------------------------------------
    */

    public function test_survey_submission_enforces_rate_limiting(): void
    {
        SystemSetting::set()->update(['turnstile_secret_key' => null]);
        $template = $this->createTemplateWithQuestion();
        $payload = $this->validPayload($template->id);

        // Realiza 5 peticiones permitidas (límite configurado en public-surveys)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // La 6ta petición debe retornar HTTP 429 Too Many Requests
        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);
        $response->assertStatus(429)
            ->assertJson([
                'message' => __('Too many requests. Please wait a moment before sending another reply.'),
            ]);
    }

    public function test_survey_submission_validates_required_patient_fields(): void
    {
        SystemSetting::set()->update(['turnstile_secret_key' => null]);
        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        unset($payload['patient']['name'], $payload['patient']['dni']);

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['patient.name', 'patient.dni']);
    }

    public function test_cannot_submit_survey_for_inactive_template(): void
    {
        SystemSetting::set()->update(['turnstile_secret_key' => null]);

        $template = SurveyTemplate::factory()->create(['is_active' => false]);
        $question = SurveyQuestion::factory()->create(['survey_template_id' => $template->id]);

        $payload = $this->validPayload($template->id, $question->id);

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => __('The requested template is not available or does not exist.'),
            ]);
    }

    public function test_handles_cloudflare_turnstile_timeout_gracefully(): void
    {
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(null, 500),
        ]);

        SystemSetting::set()->update(['turnstile_secret_key' => 'test-secret']);
        $template = $this->createTemplateWithQuestion();

        $payload = $this->validPayload($template->id);
        $payload['cf_turnstile_token'] = 'timeout-token';

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cf_turnstile_token']);
    }

    public function test_survey_submission_rejects_honeypot_spam(): void
    {
        SystemSetting::set()->update(['turnstile_secret_key' => null]);

        $template = $this->createTemplateWithQuestion();
        $payload = $this->validPayload($template->id);

        // Simula un bot rellenando el campo oculto
        $payload['website'] = 'http://spam-bot-link.com';

        $response = $this->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['website']);
    }

    public function test_blocked_ip_cannot_submit_survey(): void
    {
        config(['security.blocked_ips' => ['192.168.1.100']]);
        Cache::forget('blocked_ips_list');

        $template = $this->createTemplateWithQuestion();
        $payload = $this->validPayload($template->id);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->postJson("/api/surveys/{$template->id}/submit", $payload);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Your IP address has been blocked.']);
    }
}
