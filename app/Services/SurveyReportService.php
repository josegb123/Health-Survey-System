<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SurveyReportService
{
    public function getSettings(): SystemSetting
    {
        return SystemSetting::set();
    }

    public function getSurveysInRange(string $startDate, string $endDate): Collection
    {
        return Survey::with(['patient.insurer', 'template', 'answers.question'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->latest()
            ->get();
    }

    public function generateSurveysReport(string $startDate, string $endDate, string $period)
    {
        $surveys = $this->getSurveysInRange($startDate, $endDate);
        $settings = $this->getSettings();

        $pdf = Pdf::loadView('reports.surveys-pdf', [
            'surveys' => $surveys,
            'startDate' => Carbon::parse($startDate)->format('d/m/Y'),
            'endDate' => Carbon::parse($endDate)->format('d/m/Y'),
            'period' => $period,
            'companyName' => $settings->company_name ?? config('app.name'),
        ]);

        $pdf->setPaper('letter', 'landscape');

        return $pdf;
    }

    public function generateStatisticsReport(string $startDate, string $endDate, string $period)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $settings = $this->getSettings();

        $totalSurveys = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $averageRating = (float) Survey::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->avg('rating') ?? 0.0;

        $templateBreakdown = Survey::where('status', 'completed')
            ->whereBetween('surveys.created_at', [$start, $end])
            ->join('survey_templates', 'surveys.survey_template_id', '=', 'survey_templates.id')
            ->selectRaw('survey_templates.title, COUNT(*) as total')
            ->groupBy('survey_templates.title')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $insurerBreakdown = Survey::where('surveys.status', 'completed')
            ->whereBetween('surveys.created_at', [$start, $end])
            ->join('patients', 'surveys.patient_id', '=', 'patients.id')
            ->join('insurers', 'patients.insurer_id', '=', 'insurers.id')
            ->selectRaw('insurers.name, COUNT(*) as total')
            ->groupBy('insurers.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $dailyTrend = Survey::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $pdf = Pdf::loadView('reports.statistics-pdf', [
            'startDate' => $start->format('d/m/Y'),
            'endDate' => $end->format('d/m/Y'),
            'period' => $period,
            'totalSurveys' => $totalSurveys,
            'averageRating' => $averageRating,
            'templateBreakdown' => $templateBreakdown,
            'insurerBreakdown' => $insurerBreakdown,
            'dailyTrend' => $dailyTrend,
            'companyName' => $settings->company_name ?? config('app.name'),
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    public function generateMinistryReport(string $startDate, string $endDate, string $period): string
    {
        $surveys = $this->getSurveysInRange($startDate, $endDate);
        $settings = $this->getSettings();
        $generatedAt = now()->format('d/m/Y H:i:s');

        $lines = [];
        $lines[] = '===========================================';
        $lines[] = '  REPORTE PARA EL MINISTERIO DE SALUD';
        $lines[] = '===========================================';
        $lines[] = '';
        $lines[] = 'Empresa: '.($settings->company_name ?? config('app.name'));
        $lines[] = 'NIT: '.($settings->company_dni ?? 'N/A');
        $lines[] = 'Periodo: '.$period;
        $lines[] = 'Fecha inicio: '.Carbon::parse($startDate)->format('d/m/Y');
        $lines[] = 'Fecha fin: '.Carbon::parse($endDate)->format('d/m/Y');
        $lines[] = 'Generado: '.$generatedAt;
        $lines[] = 'Total encuestas: '.$surveys->count();
        $lines[] = '';
        $lines[] = '-------------------------------------------';
        $lines[] = '';

        if ($surveys->isEmpty()) {
            $lines[] = 'No se encontraron encuestas en el periodo seleccionado.';
            $lines[] = '';
        } else {
            foreach ($surveys as $index => $survey) {
                $lines[] = 'ENCUESTA #'.($index + 1);
                $lines[] = '  ID Interno: '.$survey->id;
                $lines[] = '  Fecha: '.$survey->created_at->format('d/m/Y H:i');
                $lines[] = '  Plantilla: '.($survey->template?->title ?? 'Eliminada');
                $lines[] = '  Calificacion: '.($survey->rating ? number_format($survey->rating, 2).' / 5.00' : 'N/A');
                $lines[] = '';
                $lines[] = '  DATOS DEL PACIENTE:';
                $lines[] = '    Nombre: '.($survey->patient?->name ?? 'Anonimo');
                $lines[] = '    Documento: '.($survey->patient?->dni ?? 'N/A');
                $lines[] = '    Email: '.($survey->patient?->email ?? 'N/A');
                $lines[] = '    Telefono: '.($survey->patient?->phone ?? 'N/A');
                $lines[] = '    Aseguradora: '.($survey->patient?->insurer?->name ?? 'N/A');
                $lines[] = '';
                $lines[] = '  RESPUESTAS:';

                foreach ($survey->answers as $answer) {
                    $question = $answer->question?->question_text ?? 'Pregunta eliminada';
                    $lines[] = '    - '.$question.': '.$answer->answer_value;
                }

                $lines[] = '';
                $lines[] = '-------------------------------------------';
                $lines[] = '';
            }
        }

        $lines[] = '--- FIN DEL REPORTE ---';

        return implode("\n", $lines);
    }
}
