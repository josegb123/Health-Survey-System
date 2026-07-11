<?php

namespace App\Services;

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
                    if ($answer->survey_question_id !== $question->id) {
                        continue;
                    }

                    $answerValue = trim($answer->answer_value);
                    $normalizedAnswer = $this->normalize($answerValue);
                    $index = null;
                    foreach ($options as $i => $opt) {
                        if ($this->normalize($opt) === $normalizedAnswer) {
                            $index = $i;
                            break;
                        }
                    }
                    if ($index !== null) {
                        $optionCounts[$index]++;
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

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n'], $value);
        return preg_replace('/\s+/', ' ', $value);
    }
}
