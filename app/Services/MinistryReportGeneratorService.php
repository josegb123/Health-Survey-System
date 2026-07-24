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

        $pipeMapping = \App\Models\MinistryReportConfig::set()->pipe_mapping ?? [];

        // Build indexed counters: key = "{question_id}_{option_index}" => count
        $indexedCounters = [];
        foreach ($questions as $question) {
            $options = $question->options ?? [];
            foreach ($options as $i => $opt) {
                $indexedCounters[$question->id . '_' . $i] = 0;
            }
        }

        foreach ($surveys as $survey) {
            foreach ($survey->answers as $answer) {
                $question = $questions->firstWhere('id', $answer->survey_question_id);
                if (! $question) {
                    continue;
                }

                $options = $question->options ?? [];
                foreach ($options as $i => $opt) {
                    $label = $opt['label'] ?? $opt;
                    if (CalculateSurveyRating::normalize($answer->answer_value) === CalculateSurveyRating::normalize($label)) {
                        $key = $question->id . '_' . $i;
                        $indexedCounters[$key] = ($indexedCounters[$key] ?? 0) + 1;
                        break;
                    }
                }
            }
        }

        $totalCounterSlots = 10;
        $result = array_fill(0, $totalCounterSlots, 0);
        $touchedSlots = array_fill(0, $totalCounterSlots, false);

        $assigned = [];
        foreach ($pipeMapping as $key => $position) {
            $position = (int) $position;
            if ($position < 1 || $position > $totalCounterSlots) {
                continue;
            }
            $idx = $position - 1;
            $result[$idx] = ($indexedCounters[$key] ?? 0);
            $touchedSlots[$idx] = true;
            $assigned[$key] = true;
        }

        $nextSlot = 0;
        foreach ($indexedCounters as $key => $count) {
            if (isset($assigned[$key])) {
                continue;
            }
            while ($nextSlot < $totalCounterSlots && $touchedSlots[$nextSlot]) {
                $nextSlot++;
            }
            if ($nextSlot >= $totalCounterSlots) {
                break;
            }
            $result[$nextSlot] = $count;
            $touchedSlots[$nextSlot] = true;
            $nextSlot++;
        }

        return implode('|', [
            $settings->registry_type ?? 3,
            $consecutive,
            $settings->entity_type ?? 'NI',
            $settings->company_dni ?? '',
            ...$result,
        ]);
    }
}
