<?php

/**
 * Debug script: traces ExcelReportService code path to find why X marks are 0.
 *
 * Usage: php debug-excel-xmarks.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Illuminate\Contracts\Console\Kernel;

echo "=== EXCEL REPORT X-MARKS DEBUG ===\n\n";

$settings = SystemSetting::set();
$templateId = $settings->default_survey_template_id;
echo 'default_survey_template_id from settings: '.($templateId ?: 'NULL')."\n";

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
echo 'Questions loaded: '.$template->surveyQuestions->count()."\n\n";

foreach ($template->surveyQuestions as $qIndex => $question) {
    echo '--- QUESTION #'.($qIndex + 1)." (ID: {$question->id}) ---\n";
    echo "  field_type: {$question->field_type}\n";
    echo "  question_text: {$question->question_text}\n";

    $options = $question->options ?? [];
    $hasOptions = in_array($question->field_type, ['radio', 'select']) && ! empty($options);
    echo '  hasOptions: '.($hasOptions ? 'YES' : 'NO')."\n";

    if ($hasOptions) {
        echo "  options (raw JSON):\n";
        foreach ($options as $oi => $opt) {
            $label = $opt['label'] ?? $opt;
            $weight = $opt['weight'] ?? '?';
            echo "    [{$oi}] label=\"{$label}\" weight={$weight} normalized=\"".CalculateSurveyRating::normalize((string) $label)."\"\n";
        }
    }

    // Get surveys for this template
    $surveys = Survey::with(['patient', 'answers'])
        ->where('survey_template_id', $template->id)
        ->where('status', 'completed')
        ->get();

    echo '  Total completed surveys: '.$surveys->count()."\n";

    if ($hasOptions && $surveys->isNotEmpty()) {
        $xCount = 0;
        foreach ($surveys as $si => $survey) {
            // Find the answer for this question
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

            $patientName = $matchedAnswer->answer_value;
            $normalizedAnswer = CalculateSurveyRating::normalize($matchedAnswer->answer_value);

            // Check against each option
            foreach ($options as $opt) {
                $label = $opt['label'] ?? $opt;
                $normalizedLabel = CalculateSurveyRating::normalize((string) $label);

                if ($normalizedAnswer === $normalizedLabel) {
                    $xCount++;
                    echo "  Survey #{$survey->id}: MATCH! answer=\"{$matchedAnswer->answer_value}\" vs option=\"{$label}\"\n";
                    break;
                }
            }

            // Also show non-matches for debugging
            if (! in_array($normalizedAnswer, array_map(fn ($o) => CalculateSurveyRating::normalize((string) ($o['label'] ?? $o)), $options))) {
                echo "  Survey #{$survey->id}: NO MATCH! answer=\"{$matchedAnswer->answer_value}\" (normalized=\"{$normalizedAnswer}\")\n";
                echo '    Options normalized: '.implode(', ', array_map(fn ($o) => CalculateSurveyRating::normalize((string) ($o['label'] ?? $o)), $options))."\n";
            }
        }
        echo "  TOTAL X MARKS for this question: {$xCount}\n";
    }

    echo "\n";
}

echo "=== DEBUG COMPLETE ===\n";
