<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class ChartsSection extends Component
{
    public string $startDate;

    public string $endDate;

    public string $period = 'month';

    public string $dateRange = '';

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
        $this->dispatch(
            'charts-update',
            dailyTrend: $this->dailyTrend,
            ratingTrend: $this->ratingTrend,
        );
    }

    private function loadCharts(DashboardMetricsService $metricsService): void
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $this->dateRange = $start->translatedFormat('j M Y').' - '.$end->translatedFormat('j M Y');

        $ratingTrendData = $metricsService->getRatingTrend($start, $end);
        $dailyTrendData = $metricsService->getDailyTrend($start, $end);

        $this->dailyTrend = [
            'labels' => collect($dailyTrendData)->pluck('date')->toArray(),
            'data' => collect($dailyTrendData)->pluck('count')->toArray(),
            'label' => __('Completed Surveys'),
            'hasData' => collect($dailyTrendData)->sum('count') > 0,
        ];

        $this->ratingTrend = [
            'labels' => collect($ratingTrendData)->pluck('date')->toArray(),
            'data' => collect($ratingTrendData)->map(function ($day) {
                return $day['avg_rating'] > 0 ? (float) number_format($day['avg_rating'], 1, '.', '') : 0;
            })->toArray(),
            'label' => __('Average Rating'),
            'hasData' => collect($ratingTrendData)->contains(fn ($day) => $day['avg_rating'] > 0),
        ];

        $templates = $metricsService->getTemplateRanking($start, $end, 5);
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
