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
            SurveyTemplateSeeder::class,
            SurveyQuestionSeeder::class,
        ]);

        // change for true or false if you need.
        $includeSurveys = app()->environment(['local', 'staging']);

        if ($includeSurveys) {
            $this->call([
                PatientSeeder::class,
                SurveySeeder::class,
                SurveyAnswerSeeder::class,
            ]);
        }

        SystemSetting::set();

        User::factory()->create([
            'name' => env('ADMIN_NAME', 'Administrador por Defecto'),
            'email' => env('ADMIN_EMAIL', 'admin@admin.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password_por_defecto')),
        ])->assignRole('admin');
    }
}
