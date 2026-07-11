<flux:modal name="view-template-flyout" flyout variant="floating" class="w-full max-w-xl">
    <div class="space-y-6 h-full flex flex-col justify-between">

        @if ($viewingTemplate)
            <div class="space-y-6 overflow-y-auto pr-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <flux:heading size="lg">{{ $viewingTemplate->title }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Static audit view for survey quality control.') }}</flux:text>
                    </div>

                    <div>
                        @if ($viewingTemplate->is_active)
                            <flux:badge size="sm" color="green" inset="top bottom">{{ __('Active') }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="red" inset="top bottom">{{ __('Inactive') }}
                            </flux:badge>
                        @endif
                    </div>
                </div>

                <flux:separator />

                <div class="space-y-4">
                    <flux:heading size="sm">
                        {{ __('Question Structure (:count)', ['count' => $viewingTemplate->questions->count()]) }}
                    </flux:heading>

                    @forelse ($viewingTemplate->questions as $question)
                        <div
                            class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg space-y-3">

                            <div class="flex items-start justify-between gap-2">
                                <span class="font-medium text-zinc-900 dark:text-white text-sm">
                                    {{ $question->order }}. {{ $question->question_text }}
                                    @if ($question->is_required)
                                        <span class="text-red-500 font-bold">*</span>
                                    @endif
                                </span>

                                <flux:badge size="sm" color="zinc" variant="subtle">
                                    @switch($question->field_type)
                                        @case('text')
                                            {{ __('Text') }}
                                        @break

                                        @case('number')
                                            {{ __('Number') }}
                                        @break

                                        @case('radio')
                                            {{ __('Single Choice (Radio)') }}
                                        @break

                                        @case('select')
                                            {{ __('Dropdown') }}
                                        @break

                                        @default
                                            {{ $question->field_type }}
                                    @endswitch
                                </flux:badge>
                            </div>

                            @if (in_array($question->field_type, ['radio', 'select']) && is_array($question->options))
                                <div class="pl-4 pt-1 space-y-1.5 border-l-2 border-zinc-200 dark:border-zinc-700">
                                    <span
                                        class="text-xs font-semibold text-zinc-400 uppercase tracking-wider block">{{ __('Configured options:') }}</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($question->options as $option)
                                            @php
                                                $label = $option['label'] ?? $option;
                                                $weight = $option['weight'] ?? null;
                                            @endphp
                                            <flux:badge size="sm" variant="filled" color="zinc"
                                                class="bg-white dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300">
                                                {{ $label }} @if ($weight !== null)
                                                    ({{ $weight }})
                                                @endif
                                            </flux:badge>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        @empty
                            <p class="text-sm text-zinc-400 italic text-center py-4">
                                {{ __('This template currently has no questions registered.') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-48">
                    <flux:text class="italic">{{ __('Loading template details...') }}</flux:text>
                </div>
            @endif

            <x-slot name="footer"
                class="flex items-center justify-end mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close View') }}</flux:button>
                </flux:modal.close>
            </x-slot>
        </div>
    </flux:modal>
