<?php

namespace Database\Seeders;
use App\Models\Patient;
use App\Models\Insurer;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Condición de control: Nos aseguramos de que existan aseguradoras en la BD antes de correr este seeder
        if (Insurer::count() === 0) {
            $this->command->warn('No insurers found. Running InsurerSeeder first...');
            $this->call(InsurerSeeder::class);
        }

        // 1. Crear 30 Pacientes que SÍ tienen aseguradora (EPS)
        Patient::factory()
            ->count(30)
            ->withInsurer() // Aplica el estado que creamos en el factory
            ->create();

        // 2. Crear 10 Pacientes Particulares (Sin EPS / insurer_id = null)
        Patient::factory()
            ->count(10)
            ->create();

    }
}
