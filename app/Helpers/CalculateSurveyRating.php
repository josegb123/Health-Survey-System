<?php

namespace App\Helpers;

use App\Models\SurveyQuestion;

class CalculateSurveyRating
{
    public static function execute(int $templateId, array $answers): ?float
    {
        $questions = SurveyQuestion::where('survey_template_id', $templateId)
            ->whereIn('field_type', ['number', 'radio', 'select'])
            ->get()
            ->keyBy('id');

        if ($questions->isEmpty()) {
            return null;
        }

        $sum = 0;
        $count = 0;

        foreach ($questions as $questionId => $question) {
            if (! isset($answers[$questionId])) {
                continue;
            }

            $rawData = $answers[$questionId];
            $value = is_array($rawData) ? ($rawData['value'] ?? null) : $rawData;

            if ($question->field_type === 'number') {
                if (is_numeric($value)) {
                    $sum += (float) $value;
                    $count++;
                }
            } else {
                $weight = self::resolveWeight($question, $value);
                if ($weight !== null) {
                    $sum += $weight;
                    $count++;
                }
            }
        }

        return $count > 0 ? round($sum / $count, 2) : null;
    }

    public static function resolveWeight(SurveyQuestion $question, string $answerValue): ?float
    {
        $options = $question->options ?? [];
        if (empty($options)) {
            return null;
        }

        $normalized = self::normalize($answerValue);
        foreach ($options as $opt) {
            if (self::normalize($opt['label'] ?? '') === $normalized) {
                return (float) ($opt['weight'] ?? 0);
            }
        }

        return null;
    }

    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n'], $value);

        return preg_replace('/\s+/', ' ', $value);
    }
}
