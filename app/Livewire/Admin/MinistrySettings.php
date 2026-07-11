<?php

namespace App\Livewire\Admin;

use App\Models\MinistryReportConfig;
use App\Models\SurveyTemplate;
use Flux\Flux;
use Livewire\Component;

class MinistrySettings extends Component
{
    public ?int $survey_template_id = null;

    public array $templates = [];

    public array $preview = [];

    public function mount(): void
    {
        $config = MinistryReportConfig::set();
        $this->survey_template_id = $config->survey_template_id;
        $this->templates = SurveyTemplate::withCount('questions')
            ->latest()
            ->get()
            ->toArray();

        $this->refreshPreview();
    }

    public function updatedSurveyTemplateId(): void
    {
        $this->refreshPreview();
    }

    public function refreshPreview(): void
    {
        $this->preview = [];

        if (! $this->survey_template_id) {
            return;
        }

        $template = SurveyTemplate::with(['surveyQuestions' => function ($q) {
            $q->whereIn('field_type', ['radio', 'select'])->orderBy('order');
        }])->find($this->survey_template_id);

        if (! $template) {
            return;
        }

        $this->preview = $template->surveyQuestions->map(function ($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'field_type' => $q->field_type,
                'options' => $q->options ?? [],
            ];
        })->toArray();
    }

    public function saveConfig(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        $this->validate([
            'survey_template_id' => 'nullable|exists:survey_templates,id',
        ]);

        $config = MinistryReportConfig::set();
        $config->update(['survey_template_id' => $this->survey_template_id]);

        Flux::toast(variant: 'success', text: __('Ministry report configuration saved.'));
    }

    public function render()
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));
        }

        return view('livewire.admin.ministry-settings');
    }
}
