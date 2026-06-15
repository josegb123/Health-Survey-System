<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id(); // Siempre un ID 1 único

            // Interfaz e Idioma
            $table->string('theme')->default('light'); // light, dark, system
            $table->string('language')->default('es_CO');

            // Seguridad y Cloudflare Turnstile (Captchas)
            $table->string('turnstile_site_key')->nullable(); // Cambiado a 'site_key' (Nombre estándar de la API)
            $table->string('turnstile_secret_key')->nullable(); // Cambiado a 'secret_key' (Nombre estándar de la API)

            // Limitador de tráfico (Rate Limiting)
            $table->integer('rate_limit_requests')->default(60); // Número de peticiones por minuto

            // Datos Legales de la Entidad (Cabeceras de reportes y facturas)
            $table->string('company_name')->default('Clinical System');
            $table->string('company_dni')->nullable(); // NIT / Rut / Identificación fiscal

            // Extras recomendados para flujos alternativos
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->integer('session_timeout_minutes')->default(30);
            $table->boolean('is_maintenance_mode')->default(false);

            // Metricas
            $table->integer('survey_monthly_goal')->default(100);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
