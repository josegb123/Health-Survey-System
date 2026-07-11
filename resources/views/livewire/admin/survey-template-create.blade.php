<div class="space-y-6 p-6 max-w-4xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ $templateId ? __('Edit Survey Template') : __('Create Survey Template') }}</flux:heading>
            <flux:text class="mt-1">
                {{ $templateId ? __('Modify the template title, questions, and their settings.') : __('Build your questionnaire by adding questions, options, and reordering them.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.survey-templates.index') }}">
                <flux:button variant="ghost">{{ __('Back') }}</flux:button>
            </a>
        </div>
    </div>

    <flux:separator />

    <form wire:submit.prevent="confirmSave" class="space-y-6">
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Template Information') }}</flux:heading>
            <div class="grid grid-cols-1 gap-4">
                <flux:input wire:model="title" label="{{ __('Survey Title') }}"
                    placeholder="{{ __('E.g.: Post-Appointment Satisfaction Survey') }}" />

                <div class="flex items-center gap-2 mt-2">
                    <flux:checkbox wire:model="is_active" label="{{ __('Publish immediately (Active)') }}" />
                </div>
            </div>
        </div>

        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <div class="flex justify-between items-center">
                <div>
                    <flux:heading size="md">{{ __('Questions') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500 mt-1">
                        {{ __('Add the questions that patients will answer.') }}
                    </flux:text>
                </div>

            </div>

            <flux:separator />

            @error('questions')
                <p class="text-sm text-red-500 font-medium">{{ $message }}</p>
            @enderror

            @if (empty($questions))
                <div class="text-center py-12 text-zinc-400">
                    <flux:icon name="clipboard-document-list" variant="outline"
                        class="h-12 w-12 mx-auto mb-3 text-zinc-300" />
                    <p class="text-sm">
                        {{ __('No questions yet. Click "Add Question" to start building your survey.') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($questions as $index => $question)
                        <div class="group relative border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden transition-all duration-200 hover:border-zinc-300 dark:hover:border-zinc-700"
                            :key="'q-'.$index" x-data="{ collapsed: false }">

                            {{-- Header with question number and reorder controls --}}
                            <div
                                class="flex items-center justify-between px-4 py-3 bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex items-center justify-center w-7 h-7 rounded-full bg-zinc-200 dark:bg-zinc-700 text-xs font-bold text-zinc-600 dark:text-zinc-300">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ $question['question_text'] ?: __('(Untitled question)') }}
                                    </span>
                                    @if ($question['is_required'])
                                        <span class="text-red-400 text-xs font-bold">*</span>
                                    @endif
                                    <flux:badge size="sm" color="zinc" variant="subtle" class="ml-1">
                                        @switch($question['field_type'])
                                            @case('text')
                                                {{ __('Text') }}
                                            @break

                                            @case('number')
                                                {{ __('Number') }}
                                            @break

                                            @case('radio')
                                                {{ __('Radio') }}
                                            @break

                                            @case('select')
                                                {{ __('Select') }}
                                            @break
                                        @endswitch
                                    </flux:badge>
                                </div>

                                <div class="flex items-center gap-1">
                                    <flux:button type="button" variant="ghost" size="xs" icon="chevron-up"
                                        wire:click="moveQuestionUp({{ $index }})" :disabled="$index === 0"
                                        class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 disabled:opacity-30" />

                                    <flux:button type="button" variant="ghost" size="xs" icon="chevron-down"
                                        wire:click="moveQuestionDown({{ $index }})"
                                        :disabled="$index === count($questions) - 1"
                                        class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 disabled:opacity-30" />

                                    <flux:separator vertical class="mx-1 h-4" />

                                    <flux:button type="button" variant="ghost" size="xs" icon="trash"
                                        color="danger" wire:click="removeQuestion({{ $index }})"
                                        class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400" />
                                </div>
                            </div>

                            {{-- Question body --}}
                            <div class="p-4 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <flux:input wire:model="questions.{{ $index }}.question_text"
                                            label="{{ __('Question') }}"
                                            placeholder="{{ __('How would you rate...?') }}" />
                                    </div>
                                    <div>
                                        <flux:select wire:model.live="questions.{{ $index }}.field_type"
                                            wire:change="handleFieldTypeChange({{ $index }}, $event.target.value)"
                                            label="{{ __('Answer Type') }}">
                                            <option value="text">{{ __('Free Text') }}</option>
                                            <option value="number">{{ __('Numeric') }}</option>
                                            <option value="radio">{{ __('Single Choice (Radio)') }}</option>
                                            <option value="select">{{ __('Dropdown (Select)') }}</option>
                                        </flux:select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <flux:checkbox wire:model="questions.{{ $index }}.is_required"
                                        label="{{ __('Required') }}" />
                                </div>

                                @if (in_array($question['field_type'], ['radio', 'select']))
                                    <div
                                        class="p-4 bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-3">
                                        <div class="flex items-center justify-between">
                                            <flux:label class="text-sm font-medium">{{ __('Answer Options') }}
                                            </flux:label>
                                            <span class="text-xs text-zinc-400">{{ count($question['options']) }}
                                                {{ __('option(s)') }}</span>
                                        </div>

                                    @if (!empty($question['options']))
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Assign a weight (0 to 5) to each option. A higher weight means a more positive answer.') }}
                                        </p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($question['options'] as $optIndex => $option)
                                                @php
                                                    $label = $option['label'] ?? $option;
                                                    $weight = $option['weight'] ?? null;
                                                @endphp
                                                <div class="flex items-center gap-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2">
                                                    <span
                                                        class="flex-1 truncate text-sm text-zinc-700 dark:text-zinc-300"
                                                        title="{{ $label }}">{{ $label }}</span>
                                                    <flux:input
                                                        type="number"
                                                        wire:model.live="questions.{{ $index }}.options.{{ $optIndex }}.weight"
                                                        wire:change="updateOptionWeight({{ $index }}, {{ $optIndex }}, $event.target.value)"
                                                        label=""
                                                        size="sm"
                                                        min="0"
                                                        max="5"
                                                        step="0.01"
                                                        class="w-16" />
                                                    <button type="button"
                                                        class="text-zinc-300 hover:text-red-500 transition-colors shrink-0"
                                                        wire:click="removeOption({{ $index }}, {{ $optIndex }})">&times;</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-zinc-400 italic">{{ __('No options added yet.') }}
                                        </p>
                                    @endif

                                        <div class="flex gap-2">
                                            <flux:input wire:model="questions.{{ $index }}.new_option_text"
                                                placeholder="{{ __('E.g.: Excellent, Good, Fair...') }}" size="sm"
                                                class="flex-1" />
                                            <flux:button type="button" size="sm" variant="subtle"
                                                wire:click="addOption({{ $index }})">{{ __('Add') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Live preview of the question --}}
                                @if ($question['question_text'])
                                    <div
                                        class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-lg">
                                        <span
                                            class="text-xs font-semibold text-blue-500 uppercase tracking-wider">{{ __('Preview') }}</span>
                                        <p class="text-sm text-zinc-800 dark:text-zinc-200 mt-1">
                                            {{ $question['question_text'] }}
                                            @if ($question['is_required'])
                                                <span class="text-red-400">*</span>
                                            @endif
                                        </p>
                                        @if (in_array($question['field_type'], ['radio', 'select']) && !empty($question['options']))
                                            <div class="mt-2 space-y-1">
                                                @foreach ($question['options'] as $opt)
                                                    <div class="flex items-center gap-2 text-sm text-zinc-500">
                                                        @if ($question['field_type'] === 'radio')
                                                            <span
                                                                class="w-3.5 h-3.5 rounded-full border-2 border-zinc-300 block"></span>
                                                        @else
                                                            <span class="text-zinc-300">-</span>
                                                        @endif
                                                        {{ $opt['label'] ?? $opt }}
                                                        @if (isset($opt['weight']))
                                                            <span class="text-xs text-zinc-400">({{ $opt['weight'] }})</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($question['field_type'] === 'text')
                                            <div
                                                class="mt-1 h-6 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded">
                                            </div>
                                        @elseif ($question['field_type'] === 'number')
                                            <div
                                                class="mt-1 h-6 w-24 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <flux:button type="button" variant="primary" color="green" icon="plus" wire:click="addQuestion">
                {{ __('Add Question') }}
            </flux:button>
        </div>


        <div class="flex items-center justify-end gap-2 pt-4">
            <a href="{{ route('admin.survey-templates.index') }}">
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </a>
            <flux:button type="submit" variant="primary" icon="check" :disabled="empty($questions)">
                {{ $templateId ? __('Update Template') : __('Save Template') }}
            </flux:button>
        </div>
    </form>

    <flux:modal name="confirm-save-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $templateId ? __('Confirm update template?') : __('Confirm save template?') }}</flux:heading>
                <flux:text class="mt-2">{{ $templateId ? __('You are about to update the template:') : __('You are about to create the template:') }} <strong
                        class="text-zinc-800 dark:text-zinc-200">{{ $title }}</strong>
                </flux:text>
                <flux:text size="sm" class="mt-2 text-zinc-500">
                    {{ __('The template will contain :count questions.', ['count' => count($questions)]) }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveTemplate" variant="primary">{{ __('Confirm and Save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
