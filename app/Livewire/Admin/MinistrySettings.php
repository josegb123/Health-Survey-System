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

    /** @var array<string, int> question_option_key => pipe_position */
    public array $pipeMapping = [];

    public function mount(): void
    {
        $config = MinistryReportConfig::set();
        $this->survey_template_id = $config->survey_template_id;
        $this->templateIsDirty = false;
        $this->templates = SurveyTemplate::withCount('questions')
            ->latest()
            ->get()
            ->toArray();

        $this->pipeMapping = $config->pipe_mapping ?? [];
        $this->refreshPreview();
    }

    public bool $templateIsDirty = false;

    public function updatedSurveyTemplateId(): void
    {
        $this->templateIsDirty = true;
        $this->pipeMapping = [];
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
        })->values()->toArray();
    }

    public function getCounterOptionsProperty(): array
    {
        if (! $this->survey_template_id) {
            return [];
        }

        $options = [];
        foreach ($this->preview as $question) {
            foreach ($question['options'] ?? [] as $i => $opt) {
                $key = $question['id'] . '_' . $i;
                $label = $opt['label'] ?? $opt;
                $options[$key] = $question['question_text'] . ' → ' . $label;
            }
        }
        return $options;
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
        $config->update([
            'survey_template_id' => $this->survey_template_id,
            'pipe_mapping' => $this->pipeMapping,
        ]);

        $this->templateIsDirty = false;

        Flux::toast(variant: 'success', text: __('Ministry report configuration saved.'));
    }

    public function render()
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));
        }

        return view('livewire.admin.ministry-settings', [
            'counterOptions' => $this->counterOptions,
        ]);
    }
}
