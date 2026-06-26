<?php

namespace App\Livewire\Admin;

use App\Models\SurveyTemplate;
use App\Services\SurveyTemplateBuilderService;
use Livewire\Component;
use Livewire\WithPagination;

class SurveyTemplateIndex extends Component
{
    use WithPagination;

    // Propiedades para hidratar modales de confirmación
    public ?int $selectedTemplateId = null;
    public string $selectedTemplateTitle = '';

    // Propiedades del Formulario Dinámico (Creación)
    public string $title = '';
    public bool $is_active = true;
    public array $questions = []; // Colección dinámica de preguntas

    public ?SurveyTemplate $viewingTemplate = null;

    // Reglas de validación para el formulario compuesto
    protected array $rules = [
        'title' => 'required|string|max:255',
        'is_active' => 'boolean',
        'questions' => 'required|array|min:1',
        'questions.*.question_text' => 'required|string|max:255',
        'questions.*.field_type' => 'required|string|in:text,number,radio,select',
        'questions.*.options' => 'nullable|array',
        'questions.*.is_required' => 'boolean',
    ];

    /**
     * Mueve una pregunta una posición hacia arriba en el set dinámico.
     */
    public function moveQuestionUp(int $index): void
    {
        if ($index === 0)
            return; // Ya es la primera, flujo alternativo bloqueado

        $adjacentIndex = $index - 1;
        $backup = $this->questions[$adjacentIndex];

        // Intercambio de posiciones (Swap)
        $this->questions[$adjacentIndex] = $this->questions[$index];
        $this->questions[$index] = $backup;
    }

    /**
     * Mueve una pregunta una posición hacia abajo en el set dinámico.
     */
    public function moveQuestionDown(int $index): void
    {
        if ($index === count($this->questions) - 1)
            return; // Ya es la última, flujo alternativo bloqueado

        $adjacentIndex = $index + 1;
        $backup = $this->questions[$adjacentIndex];

        // Intercambio de posiciones (Swap)
        $this->questions[$adjacentIndex] = $this->questions[$index];
        $this->questions[$index] = $backup;
    }

    /**
     * Carga la plantilla con sus preguntas ordenadas y levanta el Flyout de visualización.
     */
    public function viewTemplate(int $id): void
    {
        $this->viewingTemplate = SurveyTemplate::with([
            'questions' => function ($query) {
                $query->orderBy('order', 'asc');
            }
        ])->findOrFail($id);

        $this->modal('view-template-flyout')->show();
    }

    /**
     * Abre el Flyout de creación e inicializa con una pregunta vacía por defecto.
     */
    public function openCreateFlyout(): void
    {
        $this->reset(['title', 'is_active', 'questions']);
        $this->addQuestion(); // Inicia con un bloque de pregunta listo
        $this->modal('create-template-flyout')->show();
    }

    /**
     * Añade un bloque de pregunta al listado dinámico.
     */
    public function addQuestion(): void
    {
        $this->questions[] = [
            'question_text' => '',
            'field_type' => 'text',
            'options' => [],
            'is_required' => true,
            'new_option_text' => '' // Campo temporal local para agregar opciones
        ];
    }

    /**
     * Remueve un bloque de pregunta por su índice.
     */
    public function removeQuestion(int $index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions); // Reindexar el array
    }

    /**
     * Añade una opción de selección al array de la pregunta correspondiente (para radio/select).
     */
    public function addOption(int $questionIndex): void
    {
        $optionText = trim($this->questions[$questionIndex]['new_option_text'] ?? '');

        if ($optionText !== '') {
            $this->questions[$questionIndex]['options'][] = $optionText;
            $this->questions[$questionIndex]['new_option_text'] = '';
        }
    }

    /**
     * Remueve una opción específica de una pregunta.
     */
    public function removeOption(int $questionIndex, int $optionIndex): void
    {
        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
    }

    /**
     * Guarda la estructura de la plantilla usando el servicio transaccional.
     */
    public function saveTemplate(SurveyTemplateBuilderService $builderService): void
    {
        $this->validate();

        try {
            $templateData = [
                'title' => $this->title,
                'is_active' => $this->is_active,
            ];

            // Invocamos el servicio encargado del control transaccional
            $builderService->createWithQuestions($templateData, $this->questions);

            $this->modal('create-template-flyout')->close();
            $this->reset(['title', 'is_active', 'questions']);

            $this->dispatch('toast', type: 'success', text: __('Template and questions created successfully.'));

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Error processing template: ') . $e->getMessage());
        }
    }

    // Métodos de confirmación (Mantienen la lógica previa)
    public function confirmToggleStatus(int $id, string $title): void
    {
        $this->selectedTemplateId = $id;
        $this->selectedTemplateTitle = $title;
        $this->modal('status-modal')->show();
    }

    public function toggleStatus(): void
    {
        if (!$this->selectedTemplateId)
            return;
        SurveyTemplate::findOrFail($this->selectedTemplateId)->update([
            'is_active' => !SurveyTemplate::findOrFail($this->selectedTemplateId)->is_active
        ]);
        $this->modal('status-modal')->close();
        $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
        $this->dispatch('toast', type: 'success', text: __('Template status updated.'));
    }

    public function confirmDelete(int $id, string $title): void
    {
        $this->selectedTemplateId = $id;
        $this->selectedTemplateTitle = $title;
        $this->modal('delete-modal')->show();
    }

    public function deleteTemplate(): void
    {
        if (!$this->selectedTemplateId)
            return;
        $template = SurveyTemplate::findOrFail($this->selectedTemplateId);
        if ($template->surveys()->exists()) {
            $this->modal('delete-modal')->close();
            $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
            $this->dispatch('toast', type: 'error', text: __('Cannot delete a template that already has clinical responses.'));
            return;
        }
        $template->delete();
        $this->modal('delete-modal')->close();
        $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
        $this->dispatch('toast', type: 'success', text: __('Template deleted successfully.'));
    }

    public function render()
    {
        return view('livewire.admin.survey-template-index', [
            'templates' => SurveyTemplate::withCount('questions')->latest()->paginate(10)
        ]);
    }
}
