<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use Illuminate\Database\Seeder;

class SurveyAnswerSeeder extends Seeder
{
    public function run(): void
    {
        $surveys = Survey::with('template.questions')->get();

        if ($surveys->isEmpty()) {
            $this->command->warn(__('No surveys found to seed answers for. Run SurveySeeder first.'));
            return;
        }

        foreach ($surveys as $survey) {
            $questions = $survey->template->questions;

            foreach ($questions as $question) {
                $answerValue = match ($question->field_type) {
                    'number' => (string) rand(4, 5),
                    'radio', 'select' => $question->options
                        ? fake()->randomElement(array_map(fn($o) => $o['label'] ?? $o, $question->options))
                        : 'Yes',
                    default => __('Everything was excellent during the medical appointment.'),
                };

                SurveyAnswer::updateOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'survey_question_id' => $question->id,
                    ],
                    [
                        'answer_value' => $answerValue,
                        'created_at' => $survey->created_at,
                        'updated_at' => $survey->created_at,
                    ]
                );
            }
        }
    }
}
