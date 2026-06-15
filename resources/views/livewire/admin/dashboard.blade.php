<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Panel de Control') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Monitoreo analítico y rendimiento de encuestas clínicas.') }}</flux:text>
        </div>

        <div>
            <flux:select wire:model.live="period" class="w-48">
                <option value="week">{{ __('Últimos 7 días') }}</option>
                <option value="month">{{ __('Último Mes') }}</option>
                <option value="year">{{ __('Último Año') }}</option>
            </flux:select>
        </div>
    </div>

    <flux:separator />

    {{-- Inyección del componente hijo pasando las propiedades iniciales --}}
    <livewire:admin.dashboard.stats-cards :startDate="$startDate" :endDate="$endDate" />
</div>
