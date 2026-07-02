<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Completed Surveys') }}</flux:heading>
            <flux:text class="mt-1">{{ __('All surveys answered by patients.') }}</flux:text>
        </div>

        <flux:button icon="chart-bar" variant="primary" wire:click="openReportModal">
            {{ __('Reports') }}
        </flux:button>
    </div>

    <flux:table :paginate="$surveys">
        <flux:table.columns>
            <flux:table.column>{{ __('Patient') }}</flux:table.column>
            <flux:table.column>{{ __('Template') }}</flux:table.column>
            <flux:table.column>{{ __('Rating') }}</flux:table.column>
            <flux:table.column>{{ __('Date') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($surveys as $survey)
                <flux:table.row :key="$survey->id">
                    <flux:table.cell class="font-medium">
                        <div class="flex flex-col">
                            <span>{{ $survey->patient?->name ?? __('Anonymous') }}</span>
                            @if ($survey->patient?->dni)
                                <span class="text-xs text-gray-500 font-normal">
                                    {{ \App\Models\Patient::documentTypes()[$survey->patient->document_type] ?? $survey->patient->document_type }}: {{ $survey->patient->dni }}
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $survey->template?->title ?? __('Deleted Template') }}
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

                    <flux:table.cell class="text-right">
                        <flux:button size="sm" variant="subtle" icon="eye"
                            wire:click="viewSurvey({{ $survey->id }})">
                            {{ __('View Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center py-8 text-gray-400 italic">
                        {{ __('No completed surveys registered.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Report modal --}}
    @include('livewire.admin.partials._report-modal')

    {{-- Ministry config error modal --}}
    <flux:modal name="ministry-config-error-modal" class="max-w-md">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-full">
                    <flux:icon name="exclamation-triangle" variant="outline" class="h-6 w-6" />
                </div>
                <flux:heading size="lg">{{ __('Incomplete Configuration') }}</flux:heading>
            </div>

            <flux:separator />

            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ $ministryConfigError }}
            </p>

            <div class="flex gap-2 pt-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="primary">{{ __('Accept') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Detail flyout --}}
    <flux:modal name="view-survey-flyout" flyout variant="floating" class="w-full max-w-2xl">
        <div class="space-y-6 h-full flex flex-col justify-between">
            @if ($viewingSurvey)
                <div class="space-y-6 overflow-y-auto pr-2">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ __('Survey Detail') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Complete survey and patient information.') }}</flux:text>
                        </div>
                        <flux:badge size="sm" color="green" inset="top bottom">{{ __('Completed') }}</flux:badge>
                    </div>

                    <flux:separator />

                    {{-- Patient Data --}}
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Patient Data') }}</flux:heading>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Name') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->name ?? __('Anonymous') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Document Type') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ \App\Models\Patient::documentTypes()[$viewingSurvey->patient?->document_type] ?? $viewingSurvey->patient?->document_type ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Document ID') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->dni ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Email') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->email ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Phone') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->phone ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Nationality') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->nationality ?? '—' }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-zinc-400 block text-xs">{{ __('Address') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->address ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Insurer') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->patient?->insurer?->name ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <flux:separator />

                    {{-- Survey Information --}}
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Survey Information') }}</flux:heading>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Template') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->template?->title ?? __('Deleted Template') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Rating') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->rating ? number_format($viewingSurvey->rating, 2) . ' / 5.00' : '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Status') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ __('Completed') }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block text-xs">{{ __('Submission Date') }}</span>
                                <span class="text-zinc-900 dark:text-white font-medium">{{ $viewingSurvey->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <flux:separator />

                    {{-- Answers --}}
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Answers') }} ({{ $viewingSurvey->answers->count() }})</flux:heading>
                        <div class="space-y-2">
                            @forelse ($viewingSurvey->answers as $answer)
                                <div class="p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                                    <span class="text-xs text-zinc-400 block">{{ $answer->question?->question_text ?? __('Deleted question') }}</span>
                                    <span class="text-sm text-zinc-900 dark:text-white font-medium mt-0.5 block">
                                        {{ $answer->answer_value }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-zinc-400 italic">{{ __('No answers recorded.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <flux:separator />

                    {{-- Digital Signature --}}
                    <div class="space-y-3">
                        <flux:heading size="sm">{{ __('Digital Signature') }}</flux:heading>
                        @if ($viewingSurvey->signature_path)
                            <div class="border border-zinc-200 dark:border-zinc-800 rounded-lg overflow-hidden bg-white max-w-xs">
                                <img src="{{ $viewingSurvey->signatureUrl() }}" alt="{{ __('Patient signature') }}" class="w-full">
                            </div>
                        @else
                            <p class="text-sm text-zinc-400 italic">{{ __('No signature recorded.') }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-48">
                    <flux:text class="italic">{{ __('Loading...') }}</flux:text>
                </div>
            @endif

            <x-slot name="footer"
                class="flex items-center justify-end mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </x-slot>
        </div>
    </flux:modal>
</div>
