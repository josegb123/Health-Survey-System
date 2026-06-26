<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Encuestas Completadas') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Todas las encuestas respondidas por los pacientes.') }}</flux:text>
        </div>
    </div>

    <flux:table :paginate="$surveys">
        <flux:table.columns>
            <flux:table.column>{{ __('Paciente') }}</flux:table.column>
            <flux:table.column>{{ __('Plantilla') }}</flux:table.column>
            <flux:table.column>{{ __('Calificación') }}</flux:table.column>
            <flux:table.column>{{ __('Fecha') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($surveys as $survey)
                <flux:table.row :key="$survey->id">
                    <flux:table.cell class="font-medium">
                        <div class="flex flex-col">
                            <span>{{ $survey->patient?->name ?? __('Anónimo') }}</span>
                            @if ($survey->patient?->dni)
                                <span class="text-xs text-gray-500 font-normal">
                                    {{ $survey->patient->document_type ?? 'DNI' }}: {{ $survey->patient->dni }}
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $survey->template?->title ?? __('Plantilla Eliminada') }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if (is_null($survey->rating))
                            <span class="text-xs text-gray-400 italic">&mdash;</span>
                        @else
                            @php
                                $badgeColor = match (true) {
                                    $survey->rating >= 4.5 => 'success',
                                    $survey->rating >= 3.0 => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <flux:badge :color="$badgeColor" size="sm" class="font-semibold">
                                {{ number_format($survey->rating, 2) }} / 5.00
                            </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-gray-500 text-sm">
                        {{ $survey->created_at->format('d/m/Y H:i') }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-8 text-gray-400 italic">
                        {{ __('No hay encuestas completadas registradas.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
