<?php

namespace App\Services;

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SystemSetting;
use Carbon\Carbon;

class MinistryReportGeneratorService
{
    public function generate(int $templateId, string $startDate, string $endDate, int $consecutive): string
    {
        $settings = SystemSetting::set();
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $questions = SurveyQuestion::where('survey_template_id', $templateId)
            ->whereIn('field_type', ['radio', 'select'])
            ->orderBy('order')
            ->get();

        $surveys = Survey::with(['answers'])
            ->where('survey_template_id', $templateId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $counters = [];
        $totalCounterSlots = 10;

        foreach ($questions as $question) {
            $options = $question->options ?? [];
            $optionCounts = array_fill(0, count($options), 0);

            foreach ($surveys as $survey) {
                foreach ($survey->answers as $answer) {
                    if ((int) $answer->survey_question_id !== $question->id) {
                        continue;
                    }

                    foreach ($options as $i => $opt) {
                        $label = $opt['label'] ?? $opt;
                        if (CalculateSurveyRating::normalize($answer->answer_value) === CalculateSurveyRating::normalize($label)) {
                            $optionCounts[$i]++;
                            break;
                        }
                    }
                }
            }

            array_push($counters, ...$optionCounts);
        }

        $counters = array_pad($counters, $totalCounterSlots, 0);
        $counters = array_slice($counters, 0, $totalCounterSlots);

        return implode('|', [
            $settings->registry_type ?? 3,
            $consecutive,
            $settings->entity_type ?? 'NI',
            $settings->company_dni ?? '',
            ...$counters,
        ]);
    }
}
