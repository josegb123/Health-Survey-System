<?php

namespace App\Livewire\Admin;

use App\Models\MinistryReportConfig;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use App\Services\ExcelReportService;
use App\Services\MinistryReportGeneratorService;
use App\Services\SurveyReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyIndex extends Component
{
    use WithPagination;

    public ?Survey $viewingSurvey = null;

    public ?int $filterMonth = null;

    public ?int $filterQuarter = null;

    public string $reportPeriod = 'monthly';

    public string $reportStartDate = '';

    public string $reportEndDate = '';

    public int $reportYear;

    public ?int $reportMonth = null;

    public ?int $reportQuarter = null;

    public ?int $reportConsecutive = null;

    public ?int $reportTemplateId = null;

    public ?string $ministryConfigError = null;

    public array $templates = [];

    // --- Delete flow ---
    public int $selectedSurveyId = 0;

    public string $selectedSurveyName = '';

    public int $deleteStep = 0;

    public string $deleteConfirmText = '';

    public int $answerCount = 0;

    public function clearFilters(): void
    {
        $this->filterMonth = null;
        $this->filterQuarter = null;
        $this->resetPage();
    }

    public function updatedFilterMonth(): void
    {
        $this->filterQuarter = null;
        $this->resetPage();
    }

    public function updatedFilterQuarter(): void
    {
        $this->filterMonth = null;
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->reportYear = now()->year;
        $this->reportMonth = now()->month;
        $this->reportQuarter = now()->quarter;

        $this->templates = SurveyTemplate::withCount('questions')
            ->where('is_active', true)
            ->latest()
            ->get()
            ->toArray();

        $settings = SystemSetting::set();
        $this->reportTemplateId = $settings->default_survey_template_id;
    }

    public function viewSurvey(int $id): void
    {
        $this->viewingSurvey = Survey::with([
            'patient.insurer',
            'template',
            'answers.question',
        ])->findOrFail($id);

        $this->modal('view-survey-flyout')->show();
    }

    public function confirmDeleteSurvey(int $id): void
    {
        $survey = Survey::with('patient')->findOrFail($id);
        $this->selectedSurveyId = $id;
        $this->selectedSurveyName = $survey->patient?->name ?? __('Anonymous');
        $this->answerCount = $survey->answers()->count();
        $this->deleteStep = 1;
        $this->deleteConfirmText = '';
        $this->modal('delete-survey-modal')->show();
    }

    public function cancelDeleteSurvey(): void
    {
        $this->deleteStep = 0;
        $this->deleteConfirmText = '';
        $this->answerCount = 0;
        $this->selectedSurveyId = 0;
        $this->selectedSurveyName = '';
        $this->modal('delete-survey-modal')->close();
    }

    public function proceedToDeleteStep2(): void
    {
        $this->deleteStep = 2;
    }

    public function deleteSurvey(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        if ($this->selectedSurveyId === 0) {
            return;
        }

        if ($this->deleteConfirmText !== __('DELETE ALL')) {
            $this->addError('deleteConfirmText', __('The confirmation text does not match.'));

            return;
        }

        try {
            $survey = Survey::withTrashed()->with('patient')->findOrFail($this->selectedSurveyId);

            if ($survey->signature_path && Storage::disk('local')->exists($survey->signature_path)) {
                Storage::disk('local')->delete($survey->signature_path);
            }

            $survey->answers()->forceDelete();
            $patient = $survey->patient;

            $survey->forceDelete();

            if ($patient && $patient->surveys()->withTrashed()->count() === 0) {
                $patient->forceDelete();
            }

            $this->deleteStep = 0;
            $this->deleteConfirmText = '';
            $this->answerCount = 0;
            $this->selectedSurveyId = 0;
            $this->selectedSurveyName = '';
            $this->modal('delete-survey-modal')->close();
            $this->dispatch('toast', type: 'success', text: __('Survey deleted successfully.'));
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', text: __('Failed to delete survey: :error', ['error' => $e->getMessage()]));
        }
    }

    public function openReportModal(): void
    {
        $this->setReportDateRange();
        $this->reportConsecutive = null;
        $settings = SystemSetting::set();
        $this->reportTemplateId = $settings->default_survey_template_id;
        $this->modal('report-modal')->show();
    }

    public function updatedReportPeriod(): void
    {
        $this->setReportDateRange();
    }

    public function updatedReportMonth(): void
    {
        $this->setReportDateRange();
    }

    public function updatedReportQuarter(): void
    {
        $this->setReportDateRange();
    }

    public function updatedReportYear(): void
    {
        $this->setReportDateRange();
    }

    private function setReportDateRange(): void
    {
        $year = $this->reportYear ?? now()->year;

        switch ($this->reportPeriod) {
            case 'quarterly':
                $quarter = $this->reportQuarter ?? now()->quarter;
                $month = ($quarter - 1) * 3 + 1;
                $this->reportStartDate = Carbon::create($year, $month, 1)->startOfQuarter()->format('Y-m-d');
                $this->reportEndDate = Carbon::create($year, $month, 1)->endOfQuarter()->format('Y-m-d');
                break;

            case 'yearly':
                $this->reportStartDate = Carbon::create($year, 1, 1)->startOfYear()->format('Y-m-d');
                $this->reportEndDate = Carbon::create($year, 12, 31)->endOfYear()->format('Y-m-d');
                break;

            default: // monthly
                $month = $this->reportMonth ?? now()->month;
                $this->reportStartDate = Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $this->reportEndDate = Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function downloadSurveysReport(): StreamedResponse
    {
        $this->validateReportDates();

        $service = app(ExcelReportService::class);
        $spreadsheet = $service->generate($this->reportStartDate, $this->reportEndDate);

        $filename = 'reporte-encuestas-'.$this->reportStartDate.'-a-'.$this->reportEndDate.'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
        }, $filename);
    }

    public function downloadStatisticsReport(): StreamedResponse
    {
        $this->validateReportDates();

        $service = app(SurveyReportService::class);
        $pdf = $service->generateStatisticsReport($this->reportStartDate, $this->reportEndDate, $this->reportPeriod);

        $filename = 'reporte-estadisticas-'.$this->reportStartDate.'-a-'.$this->reportEndDate.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function downloadMinistryReport(): ?StreamedResponse
    {
        $this->validate([
            'reportStartDate' => 'required|date',
            'reportEndDate' => 'required|date|after_or_equal:reportStartDate',
            'reportConsecutive' => 'required|integer|min:1',
        ]);

        $settings = SystemSetting::set();
        $missing = [];

        if (empty($settings->company_dni)) {
            $missing[] = __('Tax ID / DNI (NIT)');
        }
        if (empty($settings->entity_type)) {
            $missing[] = __('Entity ID Type');
        }
        if (empty($settings->registry_type)) {
            $missing[] = __('Registry Type');
        }

        if (! empty($missing)) {
            $this->ministryConfigError = __('The following fields must be configured in Settings before exporting: :fields', [
                'fields' => implode(', ', $missing),
            ]);
            $this->modal('ministry-config-error-modal')->show();

            return null;
        }

        $config = MinistryReportConfig::set();
        if (! $config->survey_template_id) {
            $this->ministryConfigError = __('Please configure the Ministry Report settings first.');
            $this->modal('ministry-config-error-modal')->show();

            return null;
        }

        $service = app(MinistryReportGeneratorService::class);
        $content = $service->generate(
            $config->survey_template_id,
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportConsecutive
        );

        $filename = 'reporte-ministerio-'.$this->reportStartDate.'-a-'.$this->reportEndDate.'.txt';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename);
    }

    private function validateReportDates(): void
    {
        $this->validate([
            'reportStartDate' => 'required|date',
            'reportEndDate' => 'required|date|after_or_equal:reportStartDate',
        ]);
    }

    public function render(): View
    {
        $query = Survey::with(['patient', 'template'])
            ->where('status', 'completed');

        if ($this->filterMonth) {
            $query->whereMonth('completed_at', $this->filterMonth)
                ->whereYear('completed_at', now()->year);
        } elseif ($this->filterQuarter) {
            $startMonth = ($this->filterQuarter - 1) * 3 + 1;
            $query->whereMonth('completed_at', '>=', $startMonth)
                ->whereMonth('completed_at', '<=', $startMonth + 2)
                ->whereYear('completed_at', now()->year);
        }

        return view('livewire.admin.survey-index', [
            'surveys' => $query->latest()->paginate(10),
        ]);
    }
}
