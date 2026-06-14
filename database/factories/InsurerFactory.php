<?php

namespace Database\Factories;

use App\Models\Insurer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insurer>
 */
class InsurerFactory extends Factory
{
    protected $model = Insurer::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $epsNombres = [
            'Sura',
            'Sanitas',
            'Nueva EPS',
            'Salud Total',
            'Compensar',
            'Coosalud',
            'Mutual Ser',
            'Famisanar'
        ];

        return [
            'name' => $this->faker->randomElement($epsNombres) . ' ' . $this->faker->companySuffix(),
            'type' => $this->faker->randomElement(['contributory', 'subsidized']),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Estado específico para forzar que sea del régimen Contributivo.
     */
    public function contributivo(): static
    {
        return $this->state(fn(array $attributes) => [
            'regimen' => 'contributivo',
        ]);
    }

    /**
     * Estado específico para forzar que sea del régimen Subsidiado.
     */
    public function subsidiado(): static
    {
        return $this->state(fn(array $attributes) => [
            'regimen' => 'subsidiado',
        ]);
    }
}
