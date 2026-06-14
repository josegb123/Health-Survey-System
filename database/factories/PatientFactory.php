<?php

namespace Database\Factories;

use App\Models\Insurer;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dni' => $this->faker->numberBetween(10000000, 1199999999),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'nationality' => $this->faker->randomElement(['Colombiana', 'Venezolana', 'Ecuatoriana']),
            'address' => $this->faker->boolean(85) ? $this->faker->address() : null, // 15% de probabilidad de ser null
            'phone' => $this->faker->numerify('3#########'),
            'insurer_id' => null,
        ];
    }

    /**
     * Estado para forzar que el paciente tenga una aseguradora existente.
     * Si no hay aseguradoras, crea una al vuelo usando su propio factory.
     */
    public function withInsurer(): static
    {
        return $this->state(fn(array $attributes) => [
            'insurer_id' => Insurer::inRandomOrder()->first()?->id ?? Insurer::factory(),
        ]);
    }
}
