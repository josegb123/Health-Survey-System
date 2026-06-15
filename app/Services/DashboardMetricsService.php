<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SystemSetting;
use Carbon\Carbon;

class DashboardMetricsService
{
    /**
     * Calcula el total de encuestas con estado 'completed' en un rango de fechas.
     */
    public function getCompletedSurveysCount(Carbon $startDate, Carbon $endDate): int
    {
        return Survey::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Obtiene la meta mensual configurada en el sistema.
     */
    public function getMonthlyGoal(): int
    {
        // Buscamos en la tabla de configuraciones, si no existe devolvemos 100 por defecto
        $setting = SystemSetting::where('key', 'survey_monthly_goal')->first();
        return $setting ? (int) $setting->value : 100;
    }

    /**
     * Calcula la meta proporcional para el periodo y el porcentaje de cumplimiento.
     */
    public function getGoalMetrics(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $monthlyGoal = $this->getMonthlyGoal();

        // Adaptamos la meta al switch del periodo seleccionado (Mes o Trimestre)
        $calculatedGoal = match ($period) {
            'quarter' => $monthlyGoal * 3,
            default => $monthlyGoal, // 'month'
        };

        // Contamos las encuestas completadas en ese rango específico
        $completedCount = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Evitamos división por cero en el flujo alternativo
        $percentage = $calculatedGoal > 0
            ? round(($completedCount / $calculatedGoal) * 100, 1)
            : 0;

        return [
            'goal_value' => $calculatedGoal,
            'completed' => $completedCount,
            'percentage' => $percentage,
        ];
    }
}
