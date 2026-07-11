<div class="space-y-6 p-6 max-w-4xl">
    <div>
        <flux:heading size="xl">{{ __('Ministry Report Settings') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Configure which survey template and questions are used for the Ministry of Health report.') }}
        </flux:text>
    </div>

    <flux:separator />

    <form wire:submit="saveConfig" class="space-y-6">
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Template Selection') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500">
                {{ __('Select the template that contains the questions for the Ministry of Health report. Only radio and select-type questions will be counted.') }}
            </flux:text>

            <flux:select wire:model.live="survey_template_id" label="{{ __('Template') }}">
                <option value="">-- {{ __('Select a template') }} --</option>
                @foreach ($templates as $template)
                    <option value="{{ $template['id'] }}">
                        {{ $template['title'] }} ({{ $template['questions_count'] }} {{ __('questions') }})
                    </option>
                @endforeach
            </flux:select>
        </div>

        @if (!empty($preview))
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
                <div class="flex items-center gap-2">
                    <flux:icon name="document-text" variant="outline" class="h-5 w-5 text-zinc-500" />
                    <flux:heading size="md">{{ __('Question Preview') }}</flux:heading>
                </div>

                <flux:text size="sm" class="text-zinc-500">
                    {{ __('These are the questions that will be included in the report, in order. Each option becomes a counter in the pipe.') }}
                </flux:text>

                <div class="space-y-3">
                    @foreach ($preview as $index => $q)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-sm text-zinc-800 dark:text-zinc-200">
                                    {{ $index + 1 }}. {{ $q['question_text'] }}
                                </span>
                                <flux:badge size="sm" color="zinc" variant="subtle">
                                    {{ $q['field_type'] === 'radio' ? __('Radio') : __('Select') }}
                                </flux:badge>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($q['options'] as $optIndex => $option)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-full text-xs text-zinc-600 dark:text-zinc-400">
                                        <span class="font-mono text-zinc-400">{{ $optIndex + 1 }}.</span>
                                        {{ $option }}
                                    </span>
                                @endforeach
                            </div>

                            @if ($index < count($preview) - 1)
                                <div class="mt-2 flex items-center gap-2 text-xs text-zinc-400">
                                    <flux:icon name="arrow-down" class="h-3 w-3" />
                                    <span>{{ count($q['options']) }} {{ __('counter(s) in pipe') }}</span>
                                </div>
                            @else
                                <div class="mt-2 flex items-center gap-2 text-xs text-zinc-400">
                                    <flux:icon name="check" class="h-3 w-3 text-green-500" />
                                    <span>{{ count($q['options']) }} {{ __('counter(s) — end of pipe') }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @php
                    $totalCounters = collect($preview)->sum(fn($q) => count($q['options']));
                @endphp

                <div class="p-3 bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-lg">
                    <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
                        <flux:icon name="information-circle" variant="outline" class="h-4 w-4" />
                        <span>{{ __('The pipe will contain 10 counters after the header (registry_type|consecutive|entity_type|dni).') }}</span>
                    </div>
                    @if ($totalCounters < 10)
                        <div class="flex items-center gap-2 mt-1.5 text-sm text-amber-600 dark:text-amber-400">
                            <flux:icon name="exclamation-triangle" variant="outline" class="h-4 w-4" />
                            <span>{{ __('The selected questions produce :count counter(s). The remaining :remaining will be filled with 0.', ['count' => $totalCounters, 'remaining' => 10 - $totalCounters]) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($survey_template_id)
            <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl">
                <p class="text-sm text-zinc-500 italic">
                    {{ __('The selected template has no radio or select questions. Please choose a template with appropriate question types.') }}
                </p>
            </div>
        @endif

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('dashboard') }}">
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </a>
            <flux:button type="submit" variant="primary" class="px-6">
                {{ __('Save Configuration') }}
            </flux:button>
        </div>
    </form>
</div>
