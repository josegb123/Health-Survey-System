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
        // Tomamos una pregunta aleatoria o creamos una por defecto
        $question = SurveyQuestion::inRandomOrder()->first() ?? SurveyQuestion::factory();

        // Generamos datos basados en el tipo de campo de la pregunta
        $mockAnswer = match ($question->field_type) {
            'number' => (string) $this->faker->numberBetween(1, 5),
            'radio' => $this->faker->randomElement([__('Yes'), __('No')]),
            'select' => $this->faker->randomElement([__('Excellent'), __('Good'), __('Regular'), __('Bad')]),
            default => $this->faker->sentence(6), // Para tipo 'text'
        };

        return [
            'survey_id' => Survey::inRandomOrder()->first()?->id ?? Survey::factory(),
            'survey_question_id' => $question->id,
            'answer_value' => $mockAnswer,
        ];
    }
}
