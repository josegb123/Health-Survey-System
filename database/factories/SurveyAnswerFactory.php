<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyAnswer>
 */
class SurveyAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $question = SurveyQuestion::inRandomOrder()->first() ?? SurveyQuestion::factory();

        $mockAnswer = match ($question->field_type) {
            'number' => (string) $this->faker->numberBetween(1, 5),
            'radio', 'select' => $this->faker->randomElement(
                array_map(fn($opt) => $opt['label'] ?? $opt, $question->options ?? [])
            ),
            default => $this->faker->sentence(6),
        };

        return [
            'survey_id' => Survey::inRandomOrder()->first()?->id ?? Survey::factory(),
            'survey_question_id' => $question->id,
            'answer_value' => $mockAnswer,
        ];
    }
}
