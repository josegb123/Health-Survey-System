<flux:modal name="create-template-flyout" flyout variant="floating" class="w-full max-w-2xl">
    <form wire:submit="saveTemplate" class="space-y-6 h-full flex flex-col justify-between">
        <div class="space-y-6 overflow-y-auto pr-2">
            <div>
                <flux:heading size="lg">{{ __('Crear Nueva Plantilla') }}</flux:heading>
                <flux:subheading>{{ __('Define el título global y arma el set de preguntas dinámicas.') }}
                </flux:subheading>
            </div>

            {{-- Datos de la Plantilla --}}
            <div class="grid grid-cols-1 gap-4">
                <flux:input wire:model="title" label="{{ __('Título de la Encuesta') }}"
                    placeholder="{{ __('Ej: Encuesta de Satisfacción Post-Consulta') }}" />

                <div class="flex items-center gap-2 mt-2">
                    <flux:checkbox wire:model="is_active" label="{{ __('Publicar inmediatamente (Activo)') }}" />
                </div>
            </div>

            <flux:separator />

            {{-- Bloque de Preguntas Dinámicas --}}
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <flux:heading size="sm">{{ __('Preguntas del Cuestionario') }}</flux:heading>
                    <flux:button type="button" size="sm" variant="subtle" icon="plus" wire:click="addQuestion">
                        {{ __('Añadir Pregunta') }}
                    </flux:button>
                </div>

                @error('questions')
                    <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror

                <div class="space-y-3">
                    @foreach ($questions as $index => $question)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-4 transition-all duration-200"
                            :key="'q-'.$index">

                            {{-- Cabecera de la Tarjeta: Barra de herramientas integrada (No absoluta) --}}
                            <div
                                class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-800 pb-2">
                                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">
                                    {{ __('Controles de Posición') }}
                                </span>

                                {{-- Botonera Orgánica sin fondos invasivos --}}
                                <div class="flex items-center gap-1">
                                    {{-- Flecha Arriba --}}
                                    <flux:button type="button" variant="ghost" size="sm" icon="chevron-up"
                                        wire:click="moveQuestionUp({{ $index }})" :disabled="$index === 0"
                                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200" />

                                    {{-- Flecha Abajo --}}
                                    <flux:button type="button" variant="ghost" size="sm" icon="chevron-down"
                                        wire:click="moveQuestionDown({{ $index }})"
                                        :disabled="$index === count($questions) - 1"
                                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200" />

                                    <flux:separator vertical class="mx-1 h-4" />

                                    {{-- Eliminar Bloque --}}
                                    <flux:button type="button" variant="ghost" size="sm" icon="trash"
                                        color="danger" wire:click="removeQuestion({{ $index }})"
                                        class="hover:bg-red-50 dark:hover:bg-red-950/30" />
                                </div>
                            </div>

                            {{-- Contenido del Formulario de la Pregunta (Se mantiene tu cuadrícula original intacta) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <flux:input wire:model="questions.{{ $index }}.question_text"
                                        label="{{ __('Pregunta #' . ($index + 1)) }}"
                                        placeholder="{{ __('¿Cómo califica...?') }}" />
                                </div>
                                <div>
                                    <flux:select wire:model.live="questions.{{ $index }}.field_type"
                                        label="{{ __('Tipo de Campo') }}">
                                        <option value="text">{{ __('Texto Libre') }}</option>
                                        <option value="number">{{ __('Numérico') }}</option>
                                        <option value="radio">{{ __('Selección Única (Radio)') }}</option>
                                        <option value="select">{{ __('Menú Desplegable (Select)') }}</option>
                                    </flux:select>
                                </div>
                            </div>

                            {{-- Control de Obligatoriedad --}}
                            <div class="flex items-center gap-2">
                                <flux:checkbox wire:model="questions.{{ $index }}.is_required"
                                    label="{{ __('Campo Obligatorio') }}" />
                            </div>

                            {{-- Flujo Condicional de Opciones --}}
                            @if (in_array($question['field_type'], ['radio', 'select']))
                                <div
                                    class="p-3 bg-white dark:bg-zinc-950 border border-zinc-150 rounded border-dashed space-y-3">
                                    <flux:label>{{ __('Opciones de Respuesta') }}</flux:label>

                                    @if (!empty($question['options']))
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($question['options'] as $optIndex => $option)
                                                <flux:badge size="sm" color="zinc"
                                                    class="flex items-center gap-1.5 px-2 py-0.5">
                                                    {{ $option }}
                                                    <button type="button"
                                                        class="text-zinc-400 hover:text-red-500 ml-1 focus:outline-none transition-colors"
                                                        wire:click="removeOption({{ $index }}, {{ $optIndex }})">&times;</button>
                                                </flux:badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-zinc-400 italic">
                                            {{ __('No has agregado opciones todavía.') }}
                                        </p>
                                    @endif

                                    <div class="flex gap-2">
                                        <flux:input wire:model="questions.{{ $index }}.new_option_text"
                                            placeholder="{{ __('Ej: Excelente') }}" size="sm" class="flex-1" />
                                        <flux:button type="button" size="sm" variant="subtle"
                                            wire:click="addOption({{ $index }})">{{ __('Agregar') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer fijo inferior del Flyout --}}
        <div name="footer"
            class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ __('Guardar Plantilla') }}</flux:button>
        </div>
    </form>
</flux:modal>
