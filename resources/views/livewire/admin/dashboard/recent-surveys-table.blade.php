<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
    <div class="p-5 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
        <div>
            <flux:heading size="md">{{ __('Recent Surveys') }}</flux:heading>
            <flux:text size="sm">
                {{ __('Latest responses completed by patients on the public portal.') }}</flux:text>
        </div>
        <flux:button href="{{ route('admin.surveys.index') }}" variant="subtle" size="sm" icon="arrow-right" icon-trailing>
            {{ __('View All') }}
        </flux:button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-950/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Patient') }}</th>
                    <th class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Template / Questionnaire') }}</th>
                    <th
                        class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-center">
                        {{ __('Rating') }}</th>
                    <th
                        class="p-4 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-right">
                        {{ __('Submission Date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($recentSurveys as $survey)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors group">
                        <td class="p-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $survey->patient?->name ?? __('Anonymous / Not registered') }}
                                </span>
                                @if ($survey->patient?->dni)
                                    <span class="text-xs text-zinc-400">
                                        {{ \App\Models\Patient::documentTypes()[$survey->patient->document_type] ?? $survey->patient->document_type }}:
                                        {{ $survey->patient->dni }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="p-4 whitespace-nowrap">
                            <flux:badge variant="subtle" size="sm"
                                class="bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $survey->template?->title ?? __('Deleted Template') }}
                            </flux:badge>
                        </td>

                        <td class="p-4 whitespace-nowrap text-center">
                            @if (is_null($survey->rating))
                                <span class="text-xs text-zinc-400 italic">—</span>
                            @else
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

                        <td class="p-4 whitespace-nowrap text-right text-sm text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-end">
                                <span>{{ $survey->created_at->format('d/m/Y') }}</span>
                                <span class="text-xs text-zinc-400">{{ $survey->created_at->format('H:i') }}</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-zinc-400 dark:text-zinc-500 text-sm">
                            <flux:icon icon="document-text"
                                class="mx-auto h-8 w-8 text-zinc-300 dark:text-zinc-700 mb-2" />
                            {{ __('No completed surveys have been registered in the system yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
