<?php

namespace App\Livewire\Admin;

use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = 'month'; // Filtro por defecto

    public string $startDate;

    public string $endDate;

    public function mount(): void
    {
        $this->updateDates();
    }

    public function updatedPeriod(): void
    {
        $this->updateDates();

        // Notifica de forma reactiva al subcomponentes hijo
        $this->dispatch('dashboard-filter-updated', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'period' => $this->period,
        ]);
    }

    private function updateDates(): void
    {
        $now = Carbon::now();

        [$this->startDate, $this->endDate] = match ($this->period) {
            'week' => [
                $now->copy()->startOfWeek()->toDateTimeString(),
                $now->copy()->endOfWeek()->toDateTimeString(),
            ],
            'quarter' => [
                $now->copy()->startOfQuarter()->toDateTimeString(),
                $now->copy()->endOfQuarter()->toDateTimeString(),
            ],
            'year' => [
                $now->copy()->startOfYear()->toDateTimeString(),
                $now->copy()->endOfYear()->toDateTimeString(),
            ],
            default => [
                $now->copy()->startOfMonth()->toDateTimeString(),
                $now->copy()->endOfMonth()->toDateTimeString(),
            ],
        };
    }

    public function render(DashboardMetricsService $metricsService)
    {
        $recentSurveys = $metricsService->getRecentSurveys(10);

        return view(
            'livewire.admin.dashboard',
            [
                'recentSurveys' => $recentSurveys,

            ]
        );
    }
}
