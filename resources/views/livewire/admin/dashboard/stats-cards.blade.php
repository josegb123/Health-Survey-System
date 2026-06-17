<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    {{-- Tarjeta 1: Total de Encuestas Completadas (Ya implementada) --}}
    <div
        class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center gap-4 shadow-sm">
        <div class="p-3 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg">
            <flux:icon name="check-circle" variant="outline" class="h-6 w-6" />
        </div>
        <div>
            <span
                class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">{{ __('Encuestas Completadas') }}</span>
            <span
                class="text-2xl font-bold text-zinc-900 dark:text-white mt-1 block">{{ number_format($completedSurveys) }}</span>
        </div>
    </div>

    {{-- Tarjeta 2: Meta de Cumplimiento Dinámica --}}
    <div
        class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm relative flex flex-col justify-between min-h-24">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <flux:icon name="arrow-trending-up" variant="outline" class="h-6 w-6" />
                </div>
                <div>
                    <span class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">
                        @switch($period)
                            @case('week')
                                {{ __('Meta de la semana') }}
                            @break

                            @case('quarter')
                                {{ __('Meta del Trimestre') }}
                            @break

                            @case('year')
                                {{ __('Meta del Año') }}
                            @break

                            @default
                                {{ __('Meta del Mes') }}
                        @endswitch
                    </span>
                    <span class="text-2xl font-bold text-zinc-900 dark:text-white mt-1 block">
                        {{ $goalStats['completed'] ?? 0 }} / {{ $goalStats['goal_value'] ?? 0 }}
                    </span>
                </div>
            </div>

            {{-- Botón Inline para editar la meta --}}
            <flux:button variant="ghost" icon="pencil-square" size="sm" wire:click="openGoalModal"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" />
        </div>

        {{-- Barra de Progreso Visual --}}
        <div class="mt-4">
            <div class="flex justify-between items-center text-xs text-zinc-500 mb-1">
                <span>{{ __('Progreso General') }}</span>
                <span
                    class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $goalStats['percentage'] ?? 0 }}%</span>
            </div>
            <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-2 rounded-full overflow-hidden">
                <div class="bg-indigo-600 dark:bg-indigo-500 h-2 transition-all duration-500"
                    style="width: {{ min($goalStats['percentage'] ?? 0, 100) }}%"></div>
            </div>
        </div>
    </div>

    <div
        class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-center gap-4 shadow-sm">
        <div class="p-3 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg">
            <flux:icon name="check-circle" variant="outline" class="h-6 w-6" />
        </div>
        <div>
            <span
                class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">{{ __('Calificación General') }}</span>
            <span
                class="text-2xl font-bold text-zinc-900 dark:text-white mt-1 block">{{ floatval($generalRate) }}</span>
        </div>
    </div>
    {{-- Tarjeta 3: Siguiente métrica... --}}

    {{-- MODAL INLINE DE CONFIGURACIÓN DE METAS --}}
    <flux:modal name="edit-goal-modal" class="max-w-xs">
        <form wire:submit="saveGoal" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Configurar Meta') }}</flux:heading>
                <flux:subheading>
                    {{ __('Establece la meta base mensual del sistema. Los rangos trimestrales se calcularán automáticamente.') }}
                </flux:subheading>
            </div>

            <flux:input type="number" wire:model="editingGoalValue" label="{{ __('Meta Mensual (Encuestas)') }}"
                min="1" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ __('Guardar') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
