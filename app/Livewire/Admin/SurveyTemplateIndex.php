<?php

namespace App\Livewire\Admin;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Services\SurveyTemplateBuilderService;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyTemplateIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Propiedades para hidratar modales de confirmación
    public ?int $selectedTemplateId = null;

    public string $selectedTemplateTitle = '';

    // Propiedades del Formulario Dinámico (Creación)
    public string $title = '';

    public bool $is_active = true;

    public array $questions = []; // Colección dinámica de preguntas

    public ?SurveyTemplate $viewingTemplate = null;

    // --- Delete flow ---
    public int $deleteStep = 0;

    public string $deleteConfirmText = '';

    public int $deleteSurveyCount = 0;

    // --- Duplicate name ---
    public string $duplicateName = '';

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
        if ($index === 0) {
            return;
        } // Ya es la primera, flujo alternativo bloqueado

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
        if ($index === count($this->questions) - 1) {
            return;
        } // Ya es la última, flujo alternativo bloqueado

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
            },
        ])->findOrFail($id);

        $this->modal('view-template-flyout')->show();
    }

    /**
     * Abre el Flyout de creación e inicializa con una pregunta vacía por defecto.
     */
    public function openCreateFlyout(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

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
            'new_option_text' => '', // Campo temporal local para agregar opciones
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
            $this->questions[$questionIndex]['options'][] = [
                'label' => $optionText,
                'weight' => 5,
            ];
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
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        $this->validate();

        $exists = SurveyTemplate::withTrashed()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($this->title)])
            ->exists();

        if ($exists) {
            $this->duplicateName = $this->title;
            $this->modal('duplicate-name-modal')->show();

            return;
        }

        try {
            $templateData = [
                'title' => $this->title,
                'is_active' => $this->is_active,
            ];

            // Invocamos el servicio encargado del control transaccional
            $builderService->createWithQuestions($templateData, $this->questions);

            $this->modal('create-template-flyout')->close();
            $this->reset(['title', 'is_active', 'questions']);

            Flux::toast(variant: 'success', text: __('Template and questions created successfully.'));

        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('Error processing template: ').$e->getMessage());
        }
    }

    // Métodos de confirmación (Mantienen la lógica previa)
    public function confirmToggleStatus(int $id, string $title): void
    {
        $this->selectedTemplateId = $id;
        $this->selectedTemplateTitle = $title;
        $this->modal('status-modal')->show();
    }

    public function showImportModal(): void
    {
        $this->importFile = null;
        $this->modal('import-modal')->show();
    }

    public function toggleStatus(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        if (! $this->selectedTemplateId) {
            return;
        }
        $tmpl = SurveyTemplate::findOrFail($this->selectedTemplateId);
        $tmpl->update(['is_active' => ! $tmpl->is_active]);
        $this->modal('status-modal')->close();
        $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
        Flux::toast(variant: 'success', text: __('Template status updated.'));
    }

    public function confirmDelete(int $id, string $title): void
    {
        $this->selectedTemplateId = $id;
        $this->selectedTemplateTitle = $title;
        $this->deleteSurveyCount = Survey::withTrashed()->where('survey_template_id', $id)->count();
        $this->deleteStep = 1;
        $this->deleteConfirmText = '';
        $this->modal('delete-modal')->show();
    }

    public function cancelDelete(): void
    {
        $this->deleteStep = 0;
        $this->deleteConfirmText = '';
        $this->deleteSurveyCount = 0;
        $this->modal('delete-modal')->close();
        $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
    }

    public function proceedToDeleteStep2(): void
    {
        $this->deleteStep = 2;
    }

    public function deleteTemplate(): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        if (! $this->selectedTemplateId) {
            return;
        }

        if ($this->deleteSurveyCount > 0 && $this->deleteConfirmText !== __('DELETE ALL')) {
            $this->addError('deleteConfirmText', __('The confirmation text does not match.'));

            return;
        }

        try {
            $template = SurveyTemplate::withTrashed()->findOrFail($this->selectedTemplateId);

            if ($this->deleteSurveyCount > 0) {
                $surveys = Survey::withTrashed()->where('survey_template_id', $template->id)->get();

                foreach ($surveys as $survey) {
                    if ($survey->signature_path && Storage::disk('local')->exists($survey->signature_path)) {
                        Storage::disk('local')->delete($survey->signature_path);
                    }
                }
            }

            $template->forceDelete();

            $this->deleteStep = 0;
            $this->deleteConfirmText = '';
            $this->deleteSurveyCount = 0;
            $this->modal('delete-modal')->close();
            $this->reset(['selectedTemplateId', 'selectedTemplateTitle']);
            Flux::toast(variant: 'success', text: __('Template deleted successfully.'));
        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('Failed to delete template: :error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Exporta una plantilla completa como archivo JSON para descarga.
     */
    public function exportTemplate(int $id): StreamedResponse
    {
        $template = SurveyTemplate::with(['questions' => fn ($q) => $q->orderBy('order')])->findOrFail($id);

        $data = [
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'template' => [
                'title' => $template->title,
                'is_active' => $template->is_active,
            ],
            'questions' => $template->questions->map(fn (SurveyQuestion $q) => [
                'question_text' => $q->question_text,
                'field_type' => $q->field_type,
                'options' => $q->options,
                'is_required' => $q->is_required,
            ])->toArray(),
        ];

        $filename = 'plantilla-'.$template->id.'-'.str()->slug($template->title).'.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Importa una plantilla desde un archivo JSON subido.
     */
    public $importFile;

    public function importTemplate(SurveyTemplateBuilderService $builderService): void
    {
        if (! auth()->user()->isAdmin()) {
            $this->redirect(route('dashboard'));

            return;
        }

        if (! $this->importFile) {
            Flux::toast(variant: 'danger', text: __('Please select a JSON file to import.'));

            return;
        }

        try {
            $json = $this->importFile->get();
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(__('Invalid JSON file.'));
            }

            if (empty($data['template']['title']) || empty($data['questions'])) {
                throw new \Exception(__('The JSON file does not contain a valid template structure.'));
            }

            foreach ($data['questions'] as $question) {
                if (empty($question['question_text']) || empty($question['field_type'])) {
                    throw new \Exception(__('Each question must have question_text and field_type.'));
                }
            }

            $templateData = [
                'title' => $data['template']['title'],
                'is_active' => $data['template']['is_active'] ?? true,
            ];

            $exists = SurveyTemplate::withTrashed()
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($templateData['title'])])
                ->exists();

            if ($exists) {
                $this->duplicateName = $templateData['title'];
                $this->modal('import-modal')->close();
                $this->modal('duplicate-name-modal')->show();

                return;
            }

            $builderService->createWithQuestions($templateData, $data['questions']);

            $this->modal('import-modal')->close();
            $this->importFile = null;
            Flux::toast(variant: 'success', text: __('Template imported successfully.'));

        } catch (\Exception $e) {
            Flux::toast(variant: 'danger', text: __('Error importing template: ').$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.survey-template-index', [
            'templates' => SurveyTemplate::withCount('questions')->latest()->paginate(10),
        ]);
    }
}
