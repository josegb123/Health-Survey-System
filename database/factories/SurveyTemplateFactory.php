<?php

namespace Database\Factories;

use App\Models\SurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyTemplate>
 */
class SurveyTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title,
            'is_active' => fake()->boolean(70),
        ];
    }
}
