<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->handle(new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);

$templateId = (int)($argv[1] ?? 2);
$template = App\Models\SurveyTemplate::with(['surveyQuestions'])->find($templateId);

if (!$template) {
    echo "Template #{$templateId} no encontrado.\n";
    exit(1);
}

echo "=== TEMPLATE #{$template->id}: {$template->title} ===\n\n";

foreach ($template->surveyQuestions()->orderBy('order')->get() as $q) {
    echo "PREGUNTA #{$q->id} (order: {$q->order}, type: {$q->field_type})\n";
    echo "  Texto: {$q->question_text}\n";
    echo "  Opciones en DB:\n";
    foreach ($q->options ?? [] as $i => $opt) {
        $label = $opt['label'] ?? $opt;
        $norm = App\Helpers\CalculateSurveyRating::normalize((string)$label);
        echo "    [$i] label=\"{$label}\"  →  normalize=\"{$norm}\"\n";
    }

    $answers = App\Models\SurveyAnswer::where('survey_question_id', $q->id)->get();
    echo "  Respuestas guardadas (" . $answers->count() . "):\n";
    $matchCount = 0;
    $noMatchCount = 0;
    foreach ($answers as $a) {
        $norm = App\Helpers\CalculateSurveyRating::normalize((string)$a->answer_value);
        $matched = false;
        foreach ($q->options ?? [] as $opt) {
            $label = $opt['label'] ?? $opt;
            if ($norm === App\Helpers\CalculateSurveyRating::normalize((string)$label)) {
                $matched = true;
                break;
            }
        }
        if ($matched) { $matchCount++; } else { $noMatchCount++; }
        $status = $matched ? '✅' : '❌';
        echo "    {$status} survey={$a->survey_id}  answer_value=\"{$a->answer_value}\"  →  normalize=\"{$norm}\"\n";
    }
    echo "  Resumen: {$matchCount} match, {$noMatchCount} sin match\n\n";
}
