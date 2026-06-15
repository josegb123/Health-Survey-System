<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    {{-- Tarjeta 1: Total de Encuestas Completadas --}}
    <div
        class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center gap-4 shadow-sm">
        <div class="p-3 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg">
            {{-- Icono nativo de Heroicons integrado en Flux --}}
            <flux:icon name="check-circle" variant="outline" class="h-6 w-6" />
        </div>

        <div>
            <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">
                {{ __('Encuestas Completadas') }}
            </span>
            <span class="text-2xl font-bold text-zinc-900 dark:text-white mt-1 block">
                {{ number_format($completedSurveys) }}
            </span>
        </div>
    </div>

    {{-- Aquí iremos añadiendo las siguientes tarjetas en los próximos pasos... --}}
</div>
