<?php

namespace Database\Seeders;

use App\Models\Insurer;
use Illuminate\Database\Seeder;

class InsurerSeeder extends Seeder
{
    /**
     * Ejecuta las semillas en la base de datos.
     */
    public function run(): void
    {
        $insurers = [
            ['name' => 'EPS Sura - Contributivo', 'type' => 'contributory', 'is_active' => true],
            ['name' => 'EPS Sanitas - Contributivo', 'type' => 'contributory', 'is_active' => true],
            ['name' => 'Nueva EPS - Contributivo', 'type' => 'contributory', 'is_active' => true],
            ['name' => 'Nueva EPS - Subsidiado', 'type' => 'subsidized', 'is_active' => true],
            ['name' => 'Mutual Ser - Subsidiado', 'type' => 'subsidized', 'is_active' => true],
            ['name' => 'Coosalud - Subsidiado', 'type' => 'subsidized', 'is_active' => true],
        ];

        foreach ($insurers as $insurer) {
            // updateOrCreate evita duplicar filas si ejecutas el seeder múltiples veces
            Insurer::updateOrCreate(
                ['name' => $insurer['name'], 'type' => $insurer['type']],
                ['is_active' => $insurer['is_active']]
            );
        }

        Insurer::factory()->count(10)->create();
    }
}
