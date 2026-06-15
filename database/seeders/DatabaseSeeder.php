<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            InsurerSeeder::class,
            PatientSeeder::class,
            SurveyTemplateSeeder::class,
            SurveyQuestionSeeder::class,
            SurveySeeder::class,
            SurveyAnswerSeeder::class,
        ]);


        // Inicializa el registro único de configuraciones globales
        SystemSetting::set();

        $this->command->info('System settings initialized successfully.');
        // The user is created from env file vars
        User::factory()->create([
            'name' => env('ADMIN_NAME', 'Administrador por Defecto'),
            'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password_por_defecto'))
        ])->assignRole('admin');
    }
}
