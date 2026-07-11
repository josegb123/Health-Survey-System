<?php

namespace App\Console\Commands;

use App\Helpers\CalculateSurveyRating;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class DebugExcelXMarks extends Command
{
    protected $signature = 'debug:excel-xmarks';

    protected $description = 'Debug Excel report X-marks comparison';

    public function handle(): int
    {
        $this->line('=== EXCEL REPORT X-MARKS DEBUG ===');
        $this->line('');

        $settings = SystemSetting::set();
        $templateId = $settings->default_survey_template_id;
        $this->line("default_survey_template_id from settings: ".($templateId ?: 'NULL'));

        if (! $templateId) {
            $this->error('No default template configured. Aborting.');

            return 1;
        }

        $template = SurveyTemplate::with(['surveyQuestions' => function ($q) {
            $q->orderBy('order');
        }])->find($templateId);

        if (! $template) {
            $this->error("Template #{$templateId} not found. Aborting.");

            return 1;
        }

        $this->line("Template: #{$template->id} - {$template->title}");
        $this->line('Questions loaded: '.$template->surveyQuestions->count());
        $this->line('');

        foreach ($template->surveyQuestions as $qIndex => $question) {
            $this->line('--- QUESTION #'.($qIndex + 1)." (ID: {$question->id}) ---");
            $this->line("  field_type: {$question->field_type}");
            $this->line("  question_text: {$question->question_text}");

            $options = $question->options ?? [];
            $hasOptions = in_array($question->field_type, ['radio', 'select']) && ! empty($options);
            $this->line('  hasOptions: '.($hasOptions ? 'YES' : 'NO'));

            if ($hasOptions) {
                $this->line('  options (raw):');
                foreach ($options as $oi => $opt) {
                    $label = $opt['label'] ?? $opt;
                    $weight = $opt['weight'] ?? '?';
                    $this->line("    [{$oi}] label=\"{$label}\" weight={$weight} normalized=\"".CalculateSurveyRating::normalize((string) $label)."\"");
                }
            }

            $surveys = Survey::with(['patient', 'answers'])
                ->where('survey_template_id', $template->id)
                ->where('status', 'completed')
                ->get();

            $this->line('  Total completed surveys: '.$surveys->count());

            if ($hasOptions && $surveys->isNotEmpty()) {
                $xCount = 0;
                foreach ($surveys as $survey) {
                    $this->line("  Survey #{$survey->id}: answers_loaded={$survey->answers->count()}");
                    $matchedAnswer = null;
                    foreach ($survey->answers as $answer) {
                        if ((int) $answer->survey_question_id === $question->id) {
                            $matchedAnswer = $answer;
                            break;
                        }
                    }

                    if (! $matchedAnswer) {
                        $this->line("  Survey #{$survey->id}: NO ANSWER for this question");
                        continue;
                    }

                    $normalizedAnswer = CalculateSurveyRating::normalize($matchedAnswer->answer_value);
                    $labelsNormalized = array_map(fn ($o) => CalculateSurveyRating::normalize((string) ($o['label'] ?? $o)), $options);

                    if (in_array($normalizedAnswer, $labelsNormalized)) {
                        $xCount++;
                        $this->line("  Survey #{$survey->id}: MATCH! answer=\"{$matchedAnswer->answer_value}\"");
                    } else {
                        $this->line("  Survey #{$survey->id}: NO MATCH! answer=\"{$matchedAnswer->answer_value}\" (normalized=\"{$normalizedAnswer}\")");
                        $this->line('    Expected: '.implode(' | ', $labelsNormalized));
                    }
                }
                $this->line("  TOTAL X MARKS for this question: {$xCount}");
            }

            $this->line('');
        }

        $this->line('=== DEBUG COMPLETE ===');

        return 0;
    }
}
