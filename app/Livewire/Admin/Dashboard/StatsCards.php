<?php

namespace App\Livewire\Admin\Dashboard;

use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsCards extends Component
{
    public int $completedSurveys = 0;
    public string $startDate;
    public string $endDate;

    public function mount(DashboardMetricsService $metricsService, string $startDate, string $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->loadMetrics($metricsService);
    }

    /**
     * Escucha el evento global disparado por el componente padre.
     */
    #[On('dashboard-filter-updated')]
    public function refreshMetrics(DashboardMetricsService $metricsService, array $dates): void
    {
        $this->startDate = $dates['startDate'];
        $this->endDate = $dates['endDate'];
        $this->loadMetrics($metricsService);
    }

    private function loadMetrics(DashboardMetricsService $metricsService): void
    {
        // Consumimos el método aislado del servicio
        $this->completedSurveys = $metricsService->getCompletedSurveysCount(
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)
        );
    }

    public function render()
    {
        return view('livewire.admin.dashboard.stats-cards');
    }
}
