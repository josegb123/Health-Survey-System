<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Daily Trend Chart --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
        <flux:heading size="md">{{ __('Daily Survey Trend') }}</flux:heading>
        <flux:text size="sm" class="mt-1 mb-4">{{ __('Completed surveys per day in the selected period.') }}</flux:text>

        @if (count($dailyTrend) > 0)
            @php
                $maxCount = max(array_column($dailyTrend, 'count'));
                $maxCount = max($maxCount, 1);
            @endphp
            <div class="flex items-end gap-1.5 h-40 overflow-x-auto pb-1">
                @foreach ($dailyTrend as $day)
                    <div class="flex flex-col items-center min-w-[32px] flex-1">
                        <span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                            {{ $day['count'] }}
                        </span>
                        <div class="w-full rounded-t-md transition-all duration-300"
                            style="height: {{ max(($day['count'] / $maxCount) * 120, 4) }}px;
                                   background: linear-gradient(180deg, var(--color-brand-400), var(--color-brand-600));">
                        </div>
                        <span class="text-[9px] text-zinc-400 mt-1 truncate max-w-full">
                            {{ $day['date'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-40 text-zinc-400 italic text-sm">
                {{ __('No data available for this period.') }}
            </div>
        @endif
    </div>

    {{-- Rating Trend (mini chart) --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
        <flux:heading size="md">{{ __('Rating Trend') }}</flux:heading>
        <flux:text size="sm" class="mt-1 mb-4">{{ __('Average rating per day.') }}</flux:text>

        @if (count($ratingTrend) > 0 && max(array_column($ratingTrend, 'avg_rating')) > 0)
            @php
                $maxRating = max(array_column($ratingTrend, 'avg_rating'));
                $maxRating = max($maxRating, 0.1);
            @endphp
            <div class="flex items-end gap-1.5 h-40 overflow-x-auto pb-1">
                @foreach ($ratingTrend as $day)
                    <div class="flex flex-col items-center min-w-[32px] flex-1">
                        <span class="text-[10px] font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                            {{ $day['avg_rating'] > 0 ? number_format($day['avg_rating'], 1) : '' }}
                        </span>
                        <div class="w-full rounded-t-md transition-all duration-300"
                            style="height: {{ max(($day['avg_rating'] / $maxRating) * 120, 2) }}px;
                                   background: linear-gradient(180deg, #fbbf24, #f59e0b);">
                        </div>
                        <span class="text-[9px] text-zinc-400 mt-1 truncate max-w-full">
                            {{ $day['date'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-40 text-zinc-400 italic text-sm">
                {{ __('No data available for this period.') }}
            </div>
        @endif
    </div>

    {{-- Template Ranking --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
        <flux:heading size="md">{{ __('Most Answered Templates') }}</flux:heading>
        <flux:text size="sm" class="mt-1 mb-4">{{ __('Top templates by number of responses.') }}</flux:text>

        @if (count($templateRanking) > 0)
            @php
                $maxTemplate = max(array_column($templateRanking, 'total'));
                $maxTemplate = max($maxTemplate, 1);
            @endphp
            <div class="space-y-3">
                @foreach ($templateRanking as $i => $template)
                    <div>
                        <div class="flex justify-between items-center text-sm mb-1">
                            <span class="text-zinc-700 dark:text-zinc-300 truncate mr-2">
                                <span class="font-semibold text-zinc-400 mr-1">#{{ $i + 1 }}</span>
                                {{ $template['name'] }}
                            </span>
                            <span class="font-semibold text-zinc-600 dark:text-zinc-400 text-xs shrink-0">
                                {{ $template['total'] }}
                            </span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-2 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-700"
                                style="width: {{ ($template['total'] / $maxTemplate) * 100 }}%;
                                       background: linear-gradient(90deg, var(--color-brand-300), var(--color-brand-500));">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-24 text-zinc-400 italic text-sm">
                {{ __('No data available for this period.') }}
            </div>
        @endif
    </div>

    {{-- Insurer Breakdown --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
        <flux:heading size="md">{{ __('Surveys by Insurer') }}</flux:heading>
        <flux:text size="sm" class="mt-1 mb-4">{{ __('Distribution of responses by insurer.') }}</flux:text>

        @if (count($insurerBreakdown) > 0)
            @php
                $maxInsurer = max(array_column($insurerBreakdown, 'total'));
                $maxInsurer = max($maxInsurer, 1);
                $totalInsurer = array_sum(array_column($insurerBreakdown, 'total'));
            @endphp
            <div class="space-y-3">
                @foreach ($insurerBreakdown as $insurer)
                    <div>
                        <div class="flex justify-between items-center text-sm mb-1">
                            <span class="text-zinc-700 dark:text-zinc-300 truncate mr-2">
                                {{ $insurer['name'] }}
                            </span>
                            <span class="font-semibold text-zinc-600 dark:text-zinc-400 text-xs shrink-0">
                                {{ $insurer['total'] }}
                                <span class="text-zinc-400 font-normal">
                                    ({{ $totalInsurer > 0 ? round(($insurer['total'] / $totalInsurer) * 100) : 0 }}%)
                                </span>
                            </span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-2 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-700"
                                style="width: {{ ($insurer['total'] / $maxInsurer) * 100 }}%;
                                       background: linear-gradient(90deg, #818cf8, #6366f1);">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-24 text-zinc-400 italic text-sm">
                {{ __('No data available for this period.') }}
            </div>
        @endif
    </div>
</div>
