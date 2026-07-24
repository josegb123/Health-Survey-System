<?php

/**
 * Debug script: traces ExcelReportService code path to find why X marks are 0.
 *
 * Usage: php debug-excel-xmarks.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Artisan;

Artisan::call('about');

echo "=== EXCEL REPORT X-MARKS DEBUG ===\n\n";

$settings = SystemSetting::set();
$templateId = $settings->default_survey_template_id;
echo "default_survey_template_id from settings: ".($templateId ?: 'NULL')."\n";

if (! $templateId) {
    echo "ERROR: No default template configured. Aborting.\n";
    exit(1);
}

$template = SurveyTemplate::with(['surveyQuestions' => function ($q) {
    $q->orderBy('order');
}])->find($templateId);

if (! $template) {
    echo "ERROR: Template #{$templateId} not found. Aborting.\n";
    exit(1);
}

echo "Template: #{$template->id} - {$template->title}\n";
echo "Questions loaded: ".$template->surveyQuestions->count()."\n\n";

foreach ($template->surveyQuestions as $qIndex => $question) {
    echo "--- QUESTION #".($qIndex + 1)." (ID: {$question->id}) ---\n";
    echo "  field_type: {$question->field_type}\n";
    echo "  question_text: {$question->question_text}\n";

    $options = $question->options ?? [];
    $hasOptions = in_array($question->field_type, ['radio', 'select']) && ! empty($options);
    echo "  hasOptions: ".($hasOptions ? 'YES' : 'NO')."\n";

    if ($hasOptions) {
        echo "  options (raw):\n";
        foreach ($options as $oi => $opt) {
            $label = $opt['label'] ?? $opt;
            $weight = $opt['weight'] ?? '?';
            echo "    [{$oi}] label=\"{$label}\" weight={$weight} normalized=\"".CalculateSurveyRating::normalize((string) $label)."\"\n";
        }
    }

    $surveys = Survey::with(['patient', 'answers'])
        ->where('survey_template_id', $template->id)
        ->where('status', 'completed')
        ->get();

    echo "  Total completed surveys: ".$surveys->count()."\n";

    if ($hasOptions && $surveys->isNotEmpty()) {
        $xCount = 0;
        foreach ($surveys as $survey) {
            $matchedAnswer = null;
            foreach ($survey->answers as $answer) {
                if ($answer->survey_question_id === $question->id) {
                    $matchedAnswer = $answer;
                    break;
                }
            }

            if (! $matchedAnswer) {
                echo "  Survey #{$survey->id}: NO ANSWER for this question\n";
                continue;
            }

            $normalizedAnswer = CalculateSurveyRating::normalize($matchedAnswer->answer_value);
            $labelsNormalized = array_map(fn ($o) => CalculateSurveyRating::normalize((string) ($o['label'] ?? $o)), $options);

            if (in_array($normalizedAnswer, $labelsNormalized)) {
                $xCount++;
                echo "  Survey #{$survey->id}: MATCH! answer=\"{$matchedAnswer->answer_value}\"\n";
            } else {
                echo "  Survey #{$survey->id}: NO MATCH! answer=\"{$matchedAnswer->answer_value}\" (normalized=\"{$normalizedAnswer}\")\n";
                echo "    Expected: ".implode(' | ', $labelsNormalized)."\n";
            }
        }
        echo "  TOTAL X MARKS for this question: {$xCount}\n";
    }

    echo "\n";
}

echo "=== DEBUG COMPLETE ===\n";
