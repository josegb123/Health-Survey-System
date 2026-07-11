<?php

namespace App\Livewire\Admin;

use App\Models\MinistryReportConfig;
use App\Models\SystemSetting;
use Flux\Flux;
use Livewire\Component;

class SystemSettings extends Component
{
    // Propiedades enlazadas directamente con las columnas del modelo
    public string $theme = 'system';

    public string $language = 'es';

    public ?string $turnstile_site_key = null;

    public ?string $turnstile_secret_key = null;

    public int $rate_limit_requests = 60;

    public string $company_name = '';

    public string $company_dni = '';

    public string $entity_type = 'NI';

    public int $registry_type = 3;

    public ?string $mail_from_address = null;

    public ?string $mail_from_name = null;

    public int $session_timeout_minutes = 120;

    public bool $is_maintenance_mode = false;

    public int $survey_monthly_goal = 100;

    public ?string $surveys_purge_last_run = null;

    public ?int $default_survey_template_id = null;

    public int $purgeStep = 0;

    public string $purgeResult = '';

    public string $confirmText = '';

    public string $expectedConfirmText = '';

    protected array $rules = [
        'theme' => 'nullable|string|in:light,dark,system',
        'language' => 'nullable|string|in:es,en',
        'turnstile_site_key' => 'nullable|string|max:255',
        'turnstile_secret_key' => 'nullable|string|max:255',
        'rate_limit_requests' => 'nullable|integer|min:1',
        'company_name' => 'nullable|string|max:255',
        'company_dni' => 'nullable|string|max:255',
        'entity_type' => 'nullable|string|max:10',
        'registry_type' => 'nullable|integer|min:1',
        'mail_from_address' => 'nullable|email|max:255',
        'mail_from_name' => 'nullable|string|max:255',
        'session_timeout_minutes' => 'nullable|integer|min:1',
        'is_maintenance_mode' => 'nullable|boolean',
        'survey_monthly_goal' => 'nullable|integer|min:1',
        'default_survey_template_id' => 'nullable|integer|exists:survey_templates,id',
    ];

    /**
     * Hidrata las propiedades usando tu método optimizado con caché.
     */
    public function mount(): void
    {
        $settings = SystemSetting::set();

        // Rellenamos las propiedades locales de forma masiva
        $this->fill($settings->toArray());
    }

    /**
     * Guarda la configuración actualizando el registro único y limpiando la caché.
     */
    public function saveSettings(): void
    {
        $this->validate();

        // 1. Obtenemos el registro único con ID 1
        $settings = SystemSetting::set();

        // 2. Persistimos los datos modificados
        $settings->update([
            'theme' => $this->theme,
            'language' => $this->language,
            'turnstile_site_key' => $this->turnstile_site_key,
            'turnstile_secret_key' => $this->turnstile_secret_key,
            'rate_limit_requests' => $this->rate_limit_requests,
            'company_name' => $this->company_name,
            'company_dni' => $this->company_dni,
            'entity_type' => $this->entity_type,
            'registry_type' => $this->registry_type,
            'mail_from_address' => $this->mail_from_address,
            'mail_from_name' => $this->mail_from_name,
            'session_timeout_minutes' => $this->session_timeout_minutes,
            'is_maintenance_mode' => $this->is_maintenance_mode,
            'survey_monthly_goal' => $this->survey_monthly_goal,
            'default_survey_template_id' => $this->default_survey_template_id,
        ]);

        if ($this->default_survey_template_id) {
            MinistryReportConfig::set()->update([
                'survey_template_id' => $this->default_survey_template_id,
            ]);
        }

        // Tu observer estático borra la caché 'global_system_settings' en este punto.

        Flux::toast(variant: 'success', text: __('System settings updated successfully.'));
    }

    public function startPurge(): void
    {
        $this->purgeStep = 1;
        $this->purgeResult = '';
        $this->confirmText = '';
        $this->expectedConfirmText = __('DELETE ALL');
    }

    public function nextPurgeStep(): void
    {
        $this->purgeStep = 2;
    }

    public function cancelPurge(): void
    {
        $this->purgeStep = 0;
        $this->purgeResult = '';
        $this->confirmText = '';
        $this->expectedConfirmText = '';
    }

    public function executePurge(): void
    {
        if ($this->confirmText !== $this->expectedConfirmText) {
            $this->addError('confirmText', __('The confirmation text does not match.'));

            return;
        }

        $this->purgeResult = SystemSetting::purgeOldSurveys();
        $this->purgeStep = 3;
    }

    public function render()
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));
        }

        return view('livewire.admin.system-settings');
    }
}
