<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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
     * Obtiene la meta mensual configurada desde el registro único indexado.
     */
    public function getMonthlyGoal(): int
    {
        // Consumimos tu método optimizado con caché y leemos la propiedad fillable
        $settings = SystemSetting::set();

        return $settings->survey_monthly_goal ?? 100; // 100 de respaldo si viene nulo
    }

    /**
     * Calcula la meta proporcional para el periodo y el porcentaje de cumplimiento.
     */
    public function getGoalMetrics(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $monthlyGoal = $this->getMonthlyGoal();

        // Añadimos el caso para multiplicar por 12 si se selecciona el año
        $calculatedGoal = match ($period) {
            'quarter' => $monthlyGoal * 3,
            'year' => $monthlyGoal * 12, // <-- Línea añadida
            default => $monthlyGoal,
        };

        $completedCount = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $percentage = $calculatedGoal > 0
            ? round(($completedCount / $calculatedGoal) * 100, 1)
            : 0;

        return [
            'goal_value' => $calculatedGoal,
            'completed' => $completedCount,
            'percentage' => $percentage,
        ];
    }

    /*
     * Calcula la nota promedio general en un rango de fechas.
     */
    public function getGeneralRate(Carbon $startDate, Carbon $endDate): float
    {
        $start = clone $startDate->startOfDay();
        $end = clone $endDate->endOfDay();

        $average = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->avg('rating');

        return (float) ($average ?? 0.0);
    }

    /**
     * Obtiene las últimas encuestas procesadas con sus relaciones cargadas.
     */
    public function getRecentSurveys(int $limit = 5): Collection
    {
        return Survey::with(['patient', 'template'])
            ->where('status', 'completed')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Retorna el conteo de encuestas agrupado por día para un rango.
     */
    public function getDailyTrend(Carbon $startDate, Carbon $endDate): array
    {
        $rows = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $trend = [];
        $current = clone $startDate->startOfDay();
        $end = clone $endDate->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $trend[] = [
                'date' => $current->format('d/m'),
                'count' => (int) ($rows[$key] ?? 0),
            ];
            $current->addDay();
        }

        return $trend;
    }

    /**
     * Retorna el ranking de plantillas más respondidas.
     */
    public function getTemplateRanking(int $limit = 5): Collection
    {
        return Survey::where('status', 'completed')
            ->selectRaw('survey_template_id, COUNT(*) as total')
            ->with('template')
            ->groupBy('survey_template_id')
            ->orderByDesc('total')
            ->take($limit)
            ->get();
    }

    /**
     * Retorna el desglose de encuestas por aseguradora.
     */
    public function getInsurerBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $rows = Survey::where('status', 'completed')
            ->whereBetween('surveys.created_at', [$startDate, $endDate])
            ->join('patients', 'surveys.patient_id', '=', 'patients.id')
            ->join('insurers', 'patients.insurer_id', '=', 'insurers.id')
            ->selectRaw('insurers.name, COUNT(*) as total')
            ->groupBy('insurers.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        return $rows;
    }

    /**
     * Retorna el promedio de calificación por día para un rango.
     */
    public function getRatingTrend(Carbon $startDate, Carbon $endDate): array
    {
        $rows = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, AVG(rating) as avg_rating')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('avg_rating', 'date')
            ->toArray();

        $trend = [];
        $current = clone $startDate->startOfDay();
        $end = clone $endDate->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $trend[] = [
                'date' => $current->format('d/m'),
                'avg_rating' => round((float) ($rows[$key] ?? 0), 2),
            ];
            $current->addDay();
        }

        return $trend;
    }
}
