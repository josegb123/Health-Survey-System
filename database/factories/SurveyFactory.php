<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generamos fechas distribuidas en los últimos 14 meses para simular un historial real
        $createdAt = $this->faker->dateTimeBetween('-14 months', 'now');
        $status = $this->faker->randomElement(['completed', 'completed', 'draft']); // 66% de probabilidad de estar completada

        return [
            'survey_template_id' => SurveyTemplate::inRandomOrder()->first()?->id ?? SurveyTemplate::factory(),
            'patient_id' => Patient::inRandomOrder()->first()?->id ?? Patient::factory(),
            'signature_path' => $status === 'completed'
                ? 'signatures/sig_'.$this->faker->md5.'.png'
                : null,
            'status' => $status,
            'rating' => $status === 'completed'
                ? $this->faker->randomFloat(1, 1, 5) : null,
            'completed_at' => $status === 'completed' ? $createdAt : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,

        ];
    }
}
