<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Create Survey Template') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Define the template and its dynamic questions.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.survey-templates.index') }}">
                <flux:button variant="ghost">{{ __('Back') }}</flux:button>
            </a>
        </div>
    </div>

    <flux:separator />

    <form wire:submit.prevent="confirmSave" class="space-y-6">
        <div class="grid grid-cols-1 gap-4">
            <flux:input wire:model="title" label="{{ __('Survey Title') }}"
                placeholder="{{ __('E.g.: Post-Appointment Satisfaction Survey') }}" />

            <div class="flex items-center gap-2 mt-2">
                <flux:checkbox wire:model="is_active" label="{{ __('Publish immediately (Active)') }}" />
            </div>
        </div>

        <flux:separator />

        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <flux:heading size="sm">{{ __('Questionnaire Questions') }}</flux:heading>
                <flux:button type="button" size="sm" variant="subtle" icon="plus" wire:click="addQuestion">
                    {{ __('Add Question') }}
                </flux:button>
            </div>

            @error('questions')
                <p class="text-xs text-red-500 font-medium">{{ $message }}</p>
            @enderror

            <div class="space-y-3">
                @foreach ($questions as $index => $question)
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-4 transition-all duration-200"
                        :key="'q-'.$index">

                        <div
                            class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-800 pb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">
                                {{ __('Position Controls') }}
                            </span>

                            <div class="flex items-center gap-1">
                                <flux:button type="button" variant="ghost" size="sm" icon="chevron-up"
                                    wire:click="moveQuestionUp({{ $index }})" :disabled="$index === 0"
                                    class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200" />

                                <flux:button type="button" variant="ghost" size="sm" icon="chevron-down"
                                    wire:click="moveQuestionDown({{ $index }})"
                                    :disabled="$index === count($questions) - 1"
                                    class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200" />

                                <flux:separator vertical class="mx-1 h-4" />

                                <flux:button type="button" variant="ghost" size="sm" icon="trash" color="danger"
                                    wire:click="removeQuestion({{ $index }})"
                                    class="hover:bg-red-50 dark:hover:bg-red-950/30" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <flux:input wire:model="questions.{{ $index }}.question_text"
                                    label="{{ __('Question :number', ['number' => $index + 1]) }}"
                                    placeholder="{{ __('How would you rate...?') }}" />
                            </div>
                            <div>
                                <flux:select wire:model.live="questions.{{ $index }}.field_type"
                                    wire:change="handleFieldTypeChange({{ $index }}, $event.target.value)"
                                    label="{{ __('Field Type') }}">
                                    <option value="text">{{ __('Free Text') }}</option>
                                    <option value="number">{{ __('Numeric') }}</option>
                                    <option value="radio">{{ __('Single Choice (Radio)') }}</option>
                                    <option value="select">{{ __('Dropdown (Select)') }}</option>
                                </flux:select>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="questions.{{ $index }}.is_required"
                                label="{{ __('Required Field') }}" />
                        </div>

                        @if (in_array($question['field_type'], ['radio', 'select']))
                            <div
                                class="p-3 bg-white dark:bg-zinc-950 border border-zinc-150 rounded border-dashed space-y-3">
                                <flux:label>{{ __('Answer Options') }}</flux:label>

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
                                        {{ __('You have not added any options yet.') }}
                                    </p>
                                @endif

                                <div class="flex gap-2">
                                    <flux:input wire:model="questions.{{ $index }}.new_option_text"
                                        placeholder="{{ __('E.g.: Excellent') }}" size="sm" class="flex-1" />
                                    <flux:button type="button" size="sm" variant="subtle"
                                        wire:click="addOption({{ $index }})">{{ __('Add') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
            <a href="{{ route('admin.survey-templates.index') }}">
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </a>
            <flux:button type="submit" variant="primary">{{ __('Save Template') }}</flux:button>
        </div>
    </form>

    <flux:modal name="confirm-save-modal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirm save template?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('You are about to create the template:') }} <strong
                        class="text-zinc-800 dark:text-zinc-200">{{ $title }}</strong></flux:text>
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

    {{-- Confirmation handled by integrated modal; avoid browser alerts/confirm --}}
</div>
