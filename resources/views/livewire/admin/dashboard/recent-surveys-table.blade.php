<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
    {{-- Cabecera de la Tabla --}}
    <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
        <div>
            <flux:heading size="md">{{ __('Encuestas Recientes') }}</flux:heading>
            <flux:text size="sm">
                {{ __('Últimas respuestas completadas por los pacientes en el portal público.') }}</flux:text>
        </div>
        {{-- Enlace a la vista completa de encuestas --}}
        <flux:button href="{{ route('admin.surveys.index') }}" variant="subtle" size="sm" icon="arrow-right" icon-trailing>
            {{ __('Ver todas') }}
        </flux:button>
    </div>

    {{-- Contenedor de la Tabla --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Paciente') }}</th>
                    <th class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Plantilla / Cuestionario') }}</th>
                    <th
                        class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-center">
                        {{ __('Calificación (Rating)') }}</th>
                    <th
                        class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-right">
                        {{ __('Fecha de Envío') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($recentSurveys as $survey)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group">
                        {{-- Columna Paciente --}}
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $survey->patient?->name ?? __('Anónimo / No registrado') }}
                                </span>
                                @if ($survey->patient?->dni)
                                    <span class="text-xs text-zinc-400">
                                        {{ $survey->patient->document_type ?? 'DNI' }}:
                                        {{ $survey->patient->dni }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Columna Plantilla --}}
                        <td class="p-4 whitespace-nowrap">
                            <flux:badge variant="subtle" size="sm"
                                class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $survey->template?->title ?? __('Plantilla Eliminada') }}
                            </flux:badge>
                        </td>

                        {{-- Columna Calificación (Rating Dinámico) --}}
                        <td class="p-4 whitespace-nowrap text-center">
                            @if (is_null($survey->rating))
                                <span class="text-xs text-zinc-400 italic">—</span>
                            @else
                                {{-- Formateamos el badge según el rendimiento del promedio (Escala 1-5) --}}
                                @php
                                    $badgeColor = match (true) {
                                        $survey->rating >= 4.5 => 'success',
                                        $survey->rating >= 3.0 => 'warning',
                                        default => 'danger',
                                    };
                                @endphp
                                <flux:badge :variant="$badgeColor" size="sm" class="font-semibold">
                                    {{ number_format($survey->rating, 2) }} / 5.00
                                </flux:badge>
                            @endif
                        </td>

                        {{-- Columna Fecha --}}
                        <td class="p-4 whitespace-nowrap text-right text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-end">
                                <span>{{ $survey->created_at->format('d/m/Y') }}</span>
                                <span class="text-xs text-zinc-400">{{ $survey->created_at->format('H:i') }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Estado Vacío (Flujo alternativo seguro) --}}
                    <tr>
                        <td colspan="4" class="p-8 text-center text-zinc-400 dark:text-zinc-500 text-sm">
                            <flux:icon icon="document-text"
                                class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-700 mb-2" />
                            {{ __('No se han registrado encuestas completadas en el sistema todavía.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
