<?php

namespace App\Livewire\Admin;

use App\Services\SurveyTemplateBuilderService;
use Livewire\Component;

class SurveyTemplateCreate extends Component
{
  public string $title = '';

  public bool $is_active = true;

  public array $questions = [];

  // Modal helper (kept to control UI if needed)

  protected array $rules = [
    'title' => 'required|string|max:255',
    'is_active' => 'boolean',
    'questions' => 'required|array|min:1',
    'questions.*.question_text' => 'required|string|max:255',
    'questions.*.field_type' => 'required|string|in:text,number,radio,select',
    'questions.*.options' => 'nullable|array',
    'questions.*.is_required' => 'boolean',
  ];

  public function mount(): void
  {
    $this->addQuestion();
  }

  public function addQuestion(): void
  {
    $this->questions[] = [
      'question_text' => '',
      'field_type' => 'text',
      'options' => [],
      'is_required' => true,
      'new_option_text' => '',
    ];
  }

  public function removeQuestion(int $index): void
  {
    unset($this->questions[$index]);
    $this->questions = array_values($this->questions);
  }

  public function addOption(int $questionIndex): void
  {
    $optionText = trim($this->questions[$questionIndex]['new_option_text'] ?? '');

    if ($optionText !== '') {
      $this->questions[$questionIndex]['options'][] = [
        'label' => $optionText,
        'weight' => 5,
      ];
      $this->questions[$questionIndex]['new_option_text'] = '';
    }
  }

  public function removeOption(int $questionIndex, int $optionIndex): void
  {
    unset($this->questions[$questionIndex]['options'][$optionIndex]);
    $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
  }

  public function updateOptionWeight(int $questionIndex, int $optionIndex, float $weight): void
  {
    $this->questions[$questionIndex]['options'][$optionIndex]['weight'] = max(0, min(5, (float) $weight));
  }

  public function moveQuestionUp(int $index): void
  {
    if ($index === 0) {
      return;
    }

    $adjacentIndex = $index - 1;
    $backup = $this->questions[$adjacentIndex];
    $this->questions[$adjacentIndex] = $this->questions[$index];
    $this->questions[$index] = $backup;
  }

  public function moveQuestionDown(int $index): void
  {
    if ($index === count($this->questions) - 1) {
      return;
    }

    $adjacentIndex = $index + 1;
    $backup = $this->questions[$adjacentIndex];
    $this->questions[$adjacentIndex] = $this->questions[$index];
    $this->questions[$index] = $backup;
  }

  public function handleFieldTypeChange(int $index, string $type): void
  {
    $this->questions[$index]['field_type'] = $type;

    if ($type === 'select' && empty($this->questions[$index]['options'])) {
      $labels = [
        __('Very Good'),
        __('Good'),
        __('Fair'),
        __('Bad'),
        __('Very Bad'),
      ];
      $count = count($labels);
      $this->questions[$index]['options'] = array_map(fn($label, $i) => [
        'label' => $label,
        'weight' => $count > 1 ? round(5 - $i * (4 / ($count - 1)), 2) : 5,
      ], $labels, array_keys($labels));
    }
  }

  public function confirmSave(): void
  {
    $this->modal('confirm-save-modal')->show();
  }

  public function saveTemplate(): void
  {
    if (!auth()->user()->isAdmin()) {
      $this->redirect(route('dashboard'));
      return;
    }

    $this->validate();

    try {
      $templateData = [
        'title' => $this->title,
        'is_active' => $this->is_active,
      ];

      $builderService = app(SurveyTemplateBuilderService::class);
      $builderService->createWithQuestions($templateData, $this->questions);

      $this->reset(['title', 'is_active', 'questions']);
      $this->addQuestion();
      $this->showConfirmSave = false;

      $this->dispatch('toast', type: 'success', text: __('Template and questions created successfully.'));
      $this->redirect(route('admin.survey-templates.index'));

    } catch (\Exception $e) {
      $this->dispatch('toast', type: 'error', text: __('Error processing template: ') . $e->getMessage());
    }
  }

  public function render()
  {
    return view('livewire.admin.survey-template-create');
  }
}
