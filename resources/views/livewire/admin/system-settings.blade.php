<div class="space-y-6 p-6 max-w-4xl">
    <div>
        <flux:heading size="xl">{{ __('Configuración del Sistema') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Administra los parámetros generales, llaves de seguridad y metas globales de la aplicación.') }}
        </flux:text>
    </div>

    <flux:separator />

    <form wire:submit="saveSettings" class="space-y-6">

        {{-- SECCIÓN 1: Datos de la Organización --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Información Institucional') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="company_name" label="{{ __('Nombre de la Empresa / Clínica') }}" />
                <flux:input wire:model="company_dni" label="{{ __('Identificación Fiscal / DNI') }}" />
            </div>
        </div>

        {{-- SECCIÓN 2: Preferencias y Metas --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Preferencias y Métricas') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <flux:select wire:model="theme" label="{{ __('Tema por Defecto') }}">
                        <option value="light">{{ __('Claro') }}</option>
                        <option value="dark">{{ __('Oscuro') }}</option>
                        <option value="system">{{ __('Sistema') }}</option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model="language" label="{{ __('Idioma') }}">
                        <option value="es">{{ __('Español') }}</option>
                        <option value="en">{{ __('Inglés') }}</option>
                    </flux:select>
                </div>
                <div>
                    <flux:input type="number" wire:model="survey_monthly_goal"
                        label="{{ __('Meta Mensual de Encuestas') }}" min="1" />
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: Configuración de Correo --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Servicio de Correo Saliente') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="mail_from_address" type="email" label="{{ __('Dirección de Envío (From)') }}"
                    placeholder="no-reply@clinic.com" />
                <flux:input wire:model="mail_from_name" label="{{ __('Nombre del Remitente') }}"
                    placeholder="Clinic System" />
            </div>
        </div>

        {{-- SECCIÓN 4: Seguridad y Control de Acceso --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Seguridad y Restricciones') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="number" wire:model="rate_limit_requests"
                    label="{{ __('Límite de Peticiones (Rate Limit)') }}" min="1"
                    hint="{{ __('Máximo de peticiones por minuto por IP.') }}" />
                <flux:input type="number" wire:model="session_timeout_minutes"
                    label="{{ __('Tiempo Límite de Sesión (Minutos)') }}" min="1" />
            </div>

            <flux:separator class="my-2" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="turnstile_site_key" label="{{ __('Cloudflare Turnstile Site Key') }}" />
                <flux:input wire:model="turnstile_secret_key" type="password"
                    label="{{ __('Cloudflare Turnstile Secret Key') }}" viewable />
            </div>
        </div>

        {{-- SECCIÓN 5: Estado de la Aplicación --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-3">
            <flux:heading size="md">{{ __('Estado del Servidor') }}</flux:heading>
            <div class="flex flex-col gap-1">
                <flux:checkbox wire:model="is_maintenance_mode" label="{{ __('Activar Modo Mantenimiento') }}" />
                <flux:text size="sm" class="text-zinc-400 pl-6">
                    {{ __('Si se activa, el acceso público a las encuestas quedará deshabilitado temporalmente.') }}
                </flux:text>
            </div>
        </div>

        {{-- Acciones del Formulario --}}
        <div class="flex justify-end gap-2 pt-2">
            <flux:button type="submit" variant="primary" class="px-6">
                {{ __('Guardar Cambios Globales') }}
            </flux:button>
        </div>
    </form>
</div>
