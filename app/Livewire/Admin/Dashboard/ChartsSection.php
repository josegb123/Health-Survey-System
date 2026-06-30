<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Livewire\Component;

class ChartsSection extends Component
{
    public string $startDate;

    public string $endDate;

    public string $period = 'month';

    public array $dailyTrend = [];

    public array $ratingTrend = [];

    public array $templateRanking = [];

    public array $insurerBreakdown = [];

    public function mount(DashboardMetricsService $metricsService, string $startDate, string $endDate, string $period): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->period = $period;
        $this->loadCharts($metricsService);
    }

    #[On('dashboard-filter-updated')]
    public function refreshCharts(DashboardMetricsService $metricsService, array $dates): void
    {
        $this->startDate = $dates['startDate'];
        $this->endDate = $dates['endDate'];
        $this->period = $dates['period'] ?? 'month';
        $this->loadCharts($metricsService);
    }

    private function loadCharts(DashboardMetricsService $metricsService): void
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $this->dailyTrend = $metricsService->getDailyTrend($start, $end);
        $this->ratingTrend = $metricsService->getRatingTrend($start, $end);

        $templates = $metricsService->getTemplateRanking(5);
        $this->templateRanking = $templates->map(function ($item) {
            return [
                'name' => $item->template?->title ?? __('Deleted Template'),
                'total' => (int) $item->total,
            ];
        })->toArray();

        $this->insurerBreakdown = $metricsService->getInsurerBreakdown($start, $end);
    }

    public function render()
    {
        return view('livewire.admin.dashboard.charts-section');
    }
}
