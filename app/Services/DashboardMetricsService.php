<?php

namespace App\Services;

use App\Models\Survey;
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
}
