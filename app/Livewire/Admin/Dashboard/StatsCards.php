<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\SystemSetting;
use App\Services\DashboardMetricsService;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class StatsCards extends Component
{
    public int $completedSurveys = 0;

    public array $goalStats = [];

    public string $period = 'month';

    public int $editingGoalValue = 0;

    public float $generalRate = 0.0;

    public string $startDate;

    public string $endDate;

    public function mount(DashboardMetricsService $metricsService, string $startDate, string $endDate): void
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->loadMetrics($metricsService);
    }

    #[On('dashboard-filter-updated')]
    public function refreshMetrics(DashboardMetricsService $metricsService, array $dates): void
    {
        $this->startDate = $dates['startDate'];
        $this->endDate = $dates['endDate'];
        $this->period = $dates['period'] ?? 'month';
        $this->loadMetrics($metricsService);
    }

    private function loadMetrics(DashboardMetricsService $metricsService): void
    {
        $this->completedSurveys = $metricsService->getCompletedSurveysCount(
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)
        );

        $this->goalStats = $metricsService->getGoalMetrics(
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate),
            $this->period
        );

        $this->generalRate = $metricsService->getGeneralRate(
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate),
        );

    }

    public function openGoalModal(DashboardMetricsService $metricsService): void
    {
        $this->editingGoalValue = $metricsService->getMonthlyGoal();
        $this->modal('edit-goal-modal')->show();
    }

    /**
     * Guarda la nueva meta actualizando el registro único de configuración.
     */
    public function saveGoal(DashboardMetricsService $metricsService): void
    {
        $this->validate([
            'editingGoalValue' => 'required|integer|min:1',
        ]);

        // Flujo correcto para tu modelo estructurado por columnas:
        // 1. Obtenemos el Singleton (carga o crea la fila 1)
        $settings = SystemSetting::set();

        // 2. Actualizamos la columna específica de forma segura
        $settings->update([
            'survey_monthly_goal' => $this->editingGoalValue,
        ]);

        // Tu boot del modelo se encarga de Cache::forget('global_system_settings') aquí.

        $this->modal('edit-goal-modal')->close();
        $this->loadMetrics($metricsService);
        Flux::toast(variant: 'success', text: __('System goal updated in global settings.'));
    }

    public function render()
    {
        return view('livewire.admin.dashboard.stats-cards');
    }
}
