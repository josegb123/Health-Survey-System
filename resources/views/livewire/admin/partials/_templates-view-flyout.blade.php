<flux:modal name="view-template-flyout" flyout variant="floating" class="w-full max-w-xl">
    <div class="space-y-6 h-full flex flex-col justify-between">

        @if ($viewingTemplate)
            <div class="space-y-6 overflow-y-auto pr-2">
                {{-- Cabecera del Flyout --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $viewingTemplate->title }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Vista de auditoría estática para control de calidad de la encuesta.') }}</flux:text>
                    </div>

                    {{-- Badge de estado actual --}}
                    <div>
                        @if ($viewingTemplate->is_active)
                            <flux:badge size="sm" color="green" inset="top bottom">{{ __('Activo') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="red" inset="top bottom">{{ __('Inactivo') }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <flux:separator />

                {{-- Listado Estructurado de Preguntas --}}
                <div class="space-y-4">
                    <flux:heading size="sm">
                        {{ __('Estructura de Preguntas (:count)', ['count' => $viewingTemplate->questions->count()]) }}
                    </flux:heading>

                    @forelse ($viewingTemplate->questions as $question)
                        <div
                            class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-3">

                            {{-- Fila superior de la pregunta --}}
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-medium text-zinc-900 dark:text-white text-sm">
                                    {{ $question->order }}. {{ $question->question_text }}
                                    @if ($question->is_required)
                                        <span class="text-red-500 font-bold">*</span>
                                    @endif
                                </span>

                                {{-- Tipo de campo mapeado semánticamente --}}
                                <flux:badge size="sm" color="zinc" variant="subtle">
                                    @switch($question->field_type)
                                        @case('text')
                                            {{ __('Texto') }}
                                        @break

                                        @case('number')
                                            {{ __('Número') }}
                                        @break

                                        @case('radio')
                                            {{ __('Única (Radio)') }}
                                        @break

                                        @case('select')
                                            {{ __('Desplegable') }}
                                        @break

                                        @default
                                            {{ $question->field_type }}
                                    @endswitch
                                </flux:badge>
                            </div>

                            {{-- Renderizado adaptativo de las opciones JSON si aplican --}}
                            @if (in_array($question->field_type, ['radio', 'select']) && is_array($question->options))
                                <div class="pl-4 pt-1 space-y-1.5 border-l-2 border-zinc-200 dark:border-zinc-700">
                                    <span
                                        class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">{{ __('Opciones configuradas:') }}</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($question->options as $option)
                                            <flux:badge size="sm" variant="filled" color="zinc"
                                                class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300">
                                                {{ $option }}
                                            </flux:badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        @empty
                            <p class="text-sm text-zinc-400 italic text-center py-4">
                                {{ __('Esta plantilla no contiene preguntas registradas actualmente.') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            @else
                {{-- Estado de carga preventivo en lo que Livewire hidrata el componente --}}
                <div class="flex items-center justify-center h-48">
                    <flux:text class="italic">{{ __('Cargando detalles de la plantilla...') }}</flux:text>
                </div>
            @endif

            {{-- Footer estandarizado --}}
            <x-slot name="footer"
                class="flex items-center justify-end mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cerrar Vista') }}</flux:button>
                </flux:modal.close>
            </x-slot>
        </div>
    </flux:modal>
