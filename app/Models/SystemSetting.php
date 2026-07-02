<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable('theme', 'language', 'turnstile_site_key', 'turnstile_secret_key', 'rate_limit_requests', 'company_name', 'company_dni', 'entity_type', 'registry_type', 'mail_from_address', 'mail_from_name', 'session_timeout_minutes', 'is_maintenance_mode', 'survey_monthly_goal')]
class SystemSetting extends Model
{
    // Llave única para la caché del sistema
    const CACHE_KEY = 'global_system_settings';

    /**
     * Boot del modelo para limpiar la caché automáticamente si alguien actualiza los datos.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Obtiene el registro único de configuración de forma optimizada.
     * Uso en cualquier parte del software: $settings = SystemSetting::set();
     */
    public static function set(): self
    {
        // 1. Guardamos o recuperamos un array plano en lugar de la clase serializada
        $data = Cache::rememberForever(self::CACHE_KEY, function () {
            $setting = self::firstOrCreate(['id' => 1]);

            return $setting->toArray();
        });

        // 2. Hidratamos una nueva instancia del modelo con los datos del array plano
        $instance = new self;
        $instance->forceFill($data);
        $instance->exists = true; // Indicamos a Eloquent que este registro ya existe en la Base de Datos

        return $instance;
    }
}
