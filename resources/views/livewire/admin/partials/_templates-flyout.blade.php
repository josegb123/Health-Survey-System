<flux:modal name="create-template-flyout" flyout variant="floating" class="w-full max-w-2xl">
    <form wire:submit="saveTemplate" class="space-y-6 h-full flex flex-col justify-between">
        <div class="space-y-6 overflow-y-auto pr-2">
            <div>
                <flux:heading size="lg">{{ __('Create New Template') }}</flux:heading>
                <flux:subheading>{{ __('Define the global title and build the dynamic question set.') }}
                </flux:subheading>
            </div>

            <div class="p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-4">
                <flux:input wire:model="title" label="{{ __('Survey Title') }}"
                    placeholder="{{ __('E.g.: Post-Appointment Satisfaction Survey') }}" />

                <div class="flex items-center gap-2 mt-2">
                    <flux:checkbox wire:model="is_active" label="{{ __('Publish immediately (Active)') }}" />
                </div>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <div>
                        <flux:heading size="sm">{{ __('Questions') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500 mt-1">
                            {{ __('Add the questions patients will answer.') }}
                        </flux:text>
                    </div>
                    <flux:button type="button" size="sm" variant="primary" icon="plus" wire:click="addQuestion">
                        {{ __('Add Question') }}
                    </flux:button>
                </div>

                @error('questions')
                    <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
                @enderror

                @if (empty($questions))
                    <div class="text-center py-10 text-zinc-400 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                        <flux:icon name="clipboard-document-list" variant="outline" class="h-10 w-10 mx-auto mb-2 text-zinc-300" />
                        <p class="text-sm">{{ __('No questions yet. Click the button above to start.') }}</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($questions as $index => $question)
                            <div class="group relative border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden"
                                :key="'q-'.$index">

                                {{-- Question header --}}
                                <div class="flex items-center justify-between px-3 py-2 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                                    <div class="flex items-center gap-2">
                                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 truncate max-w-[120px]">
                                            {{ $question['question_text'] ?: __('New question') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-0.5">
                                        <flux:button type="button" variant="ghost" size="xs" icon="chevron-up"
                                            wire:click="moveQuestionUp({{ $index }})" :disabled="$index === 0"
                                            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 disabled:opacity-30 !p-0.5" />

                                        <flux:button type="button" variant="ghost" size="xs" icon="chevron-down"
                                            wire:click="moveQuestionDown({{ $index }})"
                                            :disabled="$index === count($questions) - 1"
                                            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 disabled:opacity-30 !p-0.5" />

                                        <flux:button type="button" variant="ghost" size="xs" icon="trash"
                                            color="danger" wire:click="removeQuestion({{ $index }})"
                                            class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400 !p-0.5" />
                                    </div>
                                </div>

                                {{-- Question body --}}
                                <div class="p-3 space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div class="md:col-span-2">
                                            <flux:input wire:model="questions.{{ $index }}.question_text"
                                                label="{{ __('Question :number', ['number' => $index + 1]) }}"
                                                placeholder="{{ __('How would you rate...?') }}" />
                                        </div>
                                        <div>
                                            <flux:select wire:model.live="questions.{{ $index }}.field_type"
                                                label="{{ __('Type') }}">
                                                <option value="text">{{ __('Text') }}</option>
                                                <option value="number">{{ __('Number') }}</option>
                                                <option value="radio">{{ __('Radio') }}</option>
                                                <option value="select">{{ __('Select') }}</option>
                                            </flux:select>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <flux:checkbox wire:model="questions.{{ $index }}.is_required"
                                            label="{{ __('Required') }}" />
                                    </div>

                                    @if (in_array($question['field_type'], ['radio', 'select']))
                                        <div class="p-3 bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-2">
                                            <div class="flex items-center justify-between">
                                                <flux:label class="text-xs">{{ __('Options') }}</flux:label>
                                                <span class="text-xs text-zinc-400">{{ count($question['options']) }}</span>
                                            </div>

                                            @if (!empty($question['options']))
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach ($question['options'] as $optIndex => $option)
                                                        @php
                                                            $label = $option['label'] ?? $option;
                                                            $weight = $option['weight'] ?? null;
                                                        @endphp
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-full text-xs text-zinc-700 dark:text-zinc-300">
                                                            {{ $label }}
                                                            @if ($weight !== null)
                                                                <span class="text-zinc-400">({{ $weight }})</span>
                                                            @endif
                                                            <button type="button"
                                                                class="text-zinc-300 hover:text-red-500 transition-colors"
                                                                wire:click="removeOption({{ $index }}, {{ $optIndex }})">&times;</button>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-xs text-zinc-400 italic">{{ __('No options yet.') }}</p>
                                            @endif

                                            <div class="flex gap-2">
                                                <flux:input wire:model="questions.{{ $index }}.new_option_text"
                                                    placeholder="{{ __('Add option...') }}" size="sm" class="flex-1" />
                                                <flux:button type="button" size="sm" variant="subtle"
                                                    wire:click="addOption({{ $index }})">{{ __('Add') }}
                                                </flux:button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div name="footer"
            class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary" :disabled="empty($questions)">{{ __('Save Template') }}</flux:button>
        </div>
    </form>
</flux:modal>