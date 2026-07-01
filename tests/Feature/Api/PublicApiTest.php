<?php

namespace Tests\Feature\Api;

use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    protected function validPayload(int $templateId): array
    {
        return [
            'patient' => [
                'name' => 'Test Patient',
                'dni' => '123456789',
                'document_type' => 'CC',
            ],
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'answers' => [
                ['question_id' => SurveyQuestion::first()->id, 'value' => 'Test answer'],
            ],
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
}
