<?php

namespace App\Livewire\Admin;

use App\Models\Survey;
use App\Services\SurveyReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyIndex extends Component
{
    use WithPagination;

    public ?Survey $viewingSurvey = null;

    public string $reportPeriod = 'monthly';

    public string $reportStartDate = '';

    public string $reportEndDate = '';

    public function viewSurvey(int $id): void
    {
        $this->viewingSurvey = Survey::with([
            'patient.insurer',
            'template',
            'answers.question',
        ])->findOrFail($id);

        $this->modal('view-survey-flyout')->show();
    }

    public function openReportModal(): void
    {
        $this->setReportDateRange();
        $this->modal('report-modal')->show();
    }

    public function updatedReportPeriod(): void
    {
        $this->setReportDateRange();
    }

    private function setReportDateRange(): void
    {
        $now = now();

        $this->reportStartDate = match ($this->reportPeriod) {
            'quarterly' => $now->copy()->startOfQuarter()->format('Y-m-d'),
            'yearly' => $now->copy()->startOfYear()->format('Y-m-d'),
            default => $now->copy()->startOfMonth()->format('Y-m-d'),
        };

        $this->reportEndDate = match ($this->reportPeriod) {
            'quarterly' => $now->copy()->endOfQuarter()->format('Y-m-d'),
            'yearly' => $now->copy()->endOfYear()->format('Y-m-d'),
            default => $now->copy()->endOfMonth()->format('Y-m-d'),
        };
    }

    public function downloadSurveysReport(): StreamedResponse
    {
        $this->validateReportDates();

        $service = app(SurveyReportService::class);
        $pdf = $service->generateSurveysReport($this->reportStartDate, $this->reportEndDate, $this->reportPeriod);

        $filename = 'reporte-encuestas-'.$this->reportStartDate.'-a-'.$this->reportEndDate.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
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

    public function downloadMinistryReport(): StreamedResponse
    {
        $this->validateReportDates();

        $service = app(SurveyReportService::class);
        $content = $service->generateMinistryReport($this->reportStartDate, $this->reportEndDate, $this->reportPeriod);

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
        return view('livewire.admin.survey-index', [
            'surveys' => Survey::with(['patient', 'template'])
                ->where('status', 'completed')
                ->latest()
                ->paginate(10),
        ]);
    }
}
