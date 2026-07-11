<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SurveyReportService
{
    const MINISTRY_TEMPLATE_TITLE = 'Recommended by the Ministry';

    public function getSettings(): SystemSetting
    {
        return SystemSetting::set();
    }

    public function getSurveysInRange(string $startDate, string $endDate): Collection
    {
        $settings = $this->getSettings();
        $templateId = $settings->default_survey_template_id;

        $query = Survey::with(['patient.insurer', 'template', 'answers.question'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()]);

        if ($templateId) {
            $query->where('survey_template_id', $templateId);
        }

        return $query->latest()->get();
    }

    public function generateSurveysReport(string $startDate, string $endDate, string $period)
    {
        $surveys = $this->getSurveysInRange($startDate, $endDate);
        $settings = $this->getSettings();

        $data = [
            'surveys' => $surveys,
            'startDate' => Carbon::parse($startDate)->format('d/m/Y'),
            'endDate' => Carbon::parse($endDate)->format('d/m/Y'),
            'period' => $period,
            'companyName' => $settings->company_name ?? config('app.name'),
        ];

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('reports.surveys-pdf', $data);
            $pdf->setPaper('letter', 'landscape');

            return $pdf;
        }

        // Fallback fake PDF for environments without the DomPDF facade (e.g., tests)
        $html = view('reports.surveys-pdf', $data)->render();

        return new class($html)
        {
            private string $html;

            public function __construct(string $html)
            {
                $this->html = $html;
            }

            public function setPaper(): void
            {
                // no-op for fake
            }

            public function output(): string
            {
                return "%PDF-FAKE\n".$this->html;
            }
        };
    }

    public function generateStatisticsReport(string $startDate, string $endDate, string $period)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $settings = $this->getSettings();
        $templateId = $settings->default_survey_template_id;

        $surveysQuery = Survey::where('status', 'completed')
            ->whereBetween('surveys.created_at', [$start, $end]);

        if ($templateId) {
            $surveysQuery->where('survey_template_id', $templateId);
        }

        $totalSurveys = (clone $surveysQuery)->count();

        $averageRating = (float) (clone $surveysQuery)->avg('rating') ?? 0.0;

        $templateBreakdown = (clone $surveysQuery)
            ->join('survey_templates', 'surveys.survey_template_id', '=', 'survey_templates.id')
            ->selectRaw('survey_templates.title, COUNT(*) as total')
            ->groupBy('survey_templates.title')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $insurerBreakdown = (clone $surveysQuery)
            ->join('patients', 'surveys.patient_id', '=', 'patients.id')
            ->join('insurers', 'patients.insurer_id', '=', 'insurers.id')
            ->selectRaw('insurers.name, COUNT(*) as total')
            ->groupBy('insurers.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();

        $dailyTrend = (clone $surveysQuery)
            ->selectRaw('DATE(surveys.created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $data = [
            'startDate' => $start->format('d/m/Y'),
            'endDate' => $end->format('d/m/Y'),
            'period' => $period,
            'totalSurveys' => $totalSurveys,
            'averageRating' => $averageRating,
            'templateBreakdown' => $templateBreakdown,
            'insurerBreakdown' => $insurerBreakdown,
            'dailyTrend' => $dailyTrend,
            'companyName' => $settings->company_name ?? config('app.name'),
        ];

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('reports.statistics-pdf', $data);
            $pdf->setPaper('letter', 'portrait');

            return $pdf;
        }

        $html = view('reports.statistics-pdf', $data)->render();

        return new class($html)
        {
            private string $html;

            public function __construct(string $html)
            {
                $this->html = $html;
            }

            public function setPaper(): void
            {
                // no-op for fake
            }

            public function output(): string
            {
                return "%PDF-FAKE\n".$this->html;
            }
        };
    }

    public function generateMinistryReport(string $startDate, string $endDate, string $period): string
    {
        $settings = $this->getSettings();

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $surveys = Survey::with(['answers.question', 'template.questions'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($surveys->isEmpty()) {
            return __('No surveys were found in the selected period.');
        }

        $expMap = [
            'MUY BUENA' => 0,
            'BUENA' => 0,
            'REGULAR' => 0,
            'MALA' => 0,
            'MUY MALA' => 0,
        ];
        $expNoAnswer = 0;

        $recMap = [
            'DEFINITIVAMENTE SÍ' => 0,
            'PROBABLEMENTE SÍ' => 0,
            'DEFINITIVAMENTE NO' => 0,
            'PROBABLEMENTE NO' => 0,
        ];
        $recNoAnswer = 0;

        foreach ($surveys as $survey) {
            $answeredExp = false;
            $answeredRec = false;

            foreach ($survey->answers as $answer) {
                $question = $answer->question ?? $survey->template?->questions
                    ->firstWhere('id', $answer->survey_question_id);

                if (! $question) {
                    continue;
                }

                if ($question->field_type === 'radio' && ! empty($question->options)) {
                    $key = mb_strtoupper(trim($answer->answer_value));

                    // Try direct match against ministry experience options
                    if (isset($expMap[$key])) {
                        $expMap[$key]++;
                        $answeredExp = true;

                        continue;
                    }

                    // Try direct match against ministry recommendation options
                    if (isset($recMap[$key])) {
                        $recMap[$key]++;
                        $answeredRec = true;

                        continue;
                    }

                    // Map Yes/No to recommendation
                    $normalized = mb_strtoupper(trim($key));
                    if (in_array($normalized, ['YES', 'SI', 'SÍ', 'Y'])) {
                        $recMap['DEFINITIVAMENTE SÍ']++;
                        $answeredRec = true;

                        continue;
                    }
                    if (in_array($normalized, ['NO', 'N'])) {
                        $recMap['DEFINITIVAMENTE NO']++;
                        $answeredRec = true;

                        continue;
                    }
                }

                // Map number-type answers (1-5 scale) to experience
                if ($question->field_type === 'number' && is_numeric($answer->answer_value)) {
                    $val = (float) $answer->answer_value;
                    $bucket = match (true) {
                        $val >= 4.5 => 'MUY BUENA',
                        $val >= 3.5 => 'BUENA',
                        $val >= 2.5 => 'REGULAR',
                        $val >= 1.5 => 'MALA',
                        default => 'MUY MALA',
                    };
                    $expMap[$bucket]++;
                    $answeredExp = true;
                }
            }

            // Fallback: use survey rating for experience if not already answered
            if (! $answeredExp && $survey->rating !== null) {
                $bucket = match (true) {
                    $survey->rating >= 4.5 => 'MUY BUENA',
                    $survey->rating >= 3.5 => 'BUENA',
                    $survey->rating >= 2.5 => 'REGULAR',
                    $survey->rating >= 1.5 => 'MALA',
                    default => 'MUY MALA',
                };
                $expMap[$bucket]++;
                $answeredExp = true;
            }

            // Fallback: derive recommendation from rating
            if (! $answeredRec && $survey->rating !== null) {
                if ($survey->rating >= 3.5) {
                    $recMap['DEFINITIVAMENTE SÍ']++;
                } else {
                    $recMap['DEFINITIVAMENTE NO']++;
                }
                $answeredRec = true;
            }

            if (! $answeredExp) {
                $expNoAnswer++;
            }
            if (! $answeredRec) {
                $recNoAnswer++;
            }
        }

        $consecutive = 1;

        return implode('|', [
            $settings->registry_type ?? 3,
            $consecutive,
            $settings->entity_type ?? 'NI',
            $settings->company_dni ?? '',
            $expMap['MUY BUENA'],
            $expMap['BUENA'],
            $expMap['REGULAR'],
            $expMap['MALA'],
            $expMap['MUY MALA'],
            $expNoAnswer,
            $recMap['DEFINITIVAMENTE SÍ'],
            $recMap['PROBABLEMENTE SÍ'],
            $recMap['DEFINITIVAMENTE NO'],
            $recMap['PROBABLEMENTE NO'],
            $recNoAnswer,
        ]);
    }
}
