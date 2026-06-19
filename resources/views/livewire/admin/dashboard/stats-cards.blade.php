<div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
        {{-- Tarjeta 1: Total de Encuestas Completadas --}}
        <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-start gap-4 shadow-sm h-full">
            <div
                class="p-3 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-xl border border-green-100 dark:border-green-900/30 shrink-0">
                <flux:icon name="check-circle" variant="outline" class="h-6 w-6" />
            </div>
            <div class="space-y-0.5">
                <span class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest block">
                    {{ __('Encuestas Completadas') }}
                </span>
                <span class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight block">
                    {{ number_format($completedSurveys) }}
                </span>
            </div>
        </div>

        {{-- Tarjeta 2: Meta de Cumplimiento Dinámica --}}
        <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex flex-col justify-between shadow-sm h-full">
            <div class="flex items-start justify-between gap-2 w-full">
                <div class="flex items-start gap-4">
                    <div
                        class="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl border border-indigo-100 dark:border-indigo-900/30 shrink-0">
                        <flux:icon name="arrow-trending-up" variant="outline" class="h-6 w-6" />
                    </div>
                    <div class="space-y-0.5">
                        <span
                            class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest block">
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
                        <span class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight block">
                            {{ $goalStats['completed'] ?? 0 }}<span
                                class="text-lg font-medium text-zinc-400 dark:text-zinc-600 mx-1">/</span>{{ $goalStats['goal_value'] ?? 0 }}
                        </span>
                    </div>
                </div>

                <flux:button variant="ghost" icon="pencil-square" size="sm" wire:click="openGoalModal"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 -mr-2 -mt-1" />
            </div>

            <div class="mt-5 pt-1">
                <div
                    class="flex justify-between items-center text-[11px] font-medium text-zinc-400 dark:text-zinc-500 mb-1.5">
                    <span>{{ __('Progreso General') }}</span>
                    <span
                        class="font-bold text-indigo-600 dark:text-indigo-400">{{ $goalStats['percentage'] ?? 0 }}%</span>
                </div>
                <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-indigo-600 dark:bg-indigo-500 h-1.5 transition-all duration-500"
                        style="width: {{ min($goalStats['percentage'] ?? 0, 100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 3: Calificación General --}}
        <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl flex items-start gap-4 shadow-xs hover:shadow-md transition-shadow duration-200 h-full">
            <div
                class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-500 dark:text-amber-400 rounded-xl border border-amber-100 dark:border-amber-900/50 shrink-0">
                <flux:icon name="star" variant="mini" class="h-6 w-6" />
            </div>
            <div class="space-y-0.5">
                <span class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest block">
                    {{ __('Calificación General') }}
                </span>
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight block">
                        {{ number_format($generalRate, 1) }}
                    </span>
                    <span class="text-xs font-medium text-zinc-400 dark:text-zinc-600">/ 5.0</span>
                </div>
            </div>
        </div>



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

</div>
