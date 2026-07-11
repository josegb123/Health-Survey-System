<?php

use App\Models\SurveyQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->decimal('weighted_value', 5, 2)->nullable()->after('answer_value');
        });

        foreach (SurveyQuestion::whereIn('field_type', ['radio', 'select'])->cursor() as $question) {
            $options = $question->options ?? [];
            if (empty($options)) {
                continue;
            }

            // Check if already converted (first element is object with label)
            $first = $options[0] ?? null;
            if (is_array($first) && isset($first['label'])) {
                continue;
            }

            $count = count($options);
            $converted = [];
            foreach ($options as $i => $opt) {
                $weight = $count > 1
                    ? round(5 - $i * (4 / ($count - 1)), 2)
                    : 5;
                $converted[] = [
                    'label' => is_string($opt) ? $opt : ($opt['label'] ?? ''),
                    'weight' => (float) $weight,
                ];
            }

            $question->options = $converted;
            $question->save();
        }
    }

    public function down(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropColumn('weighted_value');
        });

        foreach (SurveyQuestion::whereIn('field_type', ['radio', 'select'])->cursor() as $question) {
            $options = $question->options ?? [];
            if (empty($options)) {
                continue;
            }

            $first = $options[0] ?? null;
            if (!is_array($first) || !isset($first['label'])) {
                continue;
            }

            $question->options = array_map(fn($opt) => $opt['label'], $options);
            $question->save();
        }
    }
};
