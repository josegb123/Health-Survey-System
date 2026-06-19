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
            'period' => $this->period, // <-- Línea crucial añadida
        ]);
    }

    private function updateDates(): void
    {
        $this->endDate = Carbon::now()->endOfDay()->toDateTimeString();

        $this->startDate = match ($this->period) {
            'week' => Carbon::now()->subDays(7)->startOfDay()->toDateTimeString(),
            'quarter' => Carbon::now()->subMonths(3)->startOfDay()->toDateTimeString(),
            'year' => Carbon::now()->subYear()->startOfDay()->toDateTimeString(),
            default => Carbon::now()->subMonth()->startOfDay()->toDateTimeString(),
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
