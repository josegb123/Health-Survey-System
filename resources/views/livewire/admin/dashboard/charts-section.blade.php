<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <section class="min-w-0 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <header class="mb-4">
            <flux:heading size="md">{{ __('Completed surveys') }}</flux:heading>
            <flux:text size="sm" class="mt-1">{{ __('Daily completions during :dateRange.', ['dateRange' => $dateRange]) }}</flux:text>
        </header>

        <div wire:ignore x-data="chartComponent(@js($dailyTrend), 'bar')"
            x-on:charts-update.window="updateChart($event.detail.dailyTrend)" class="relative h-64">
            <canvas x-ref="canvas" role="img" aria-label="{{ __('Completed surveys per day') }}"></canvas>
            <div x-cloak x-show="!hasData"
                class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/90 px-6 text-center text-sm text-zinc-500 dark:bg-zinc-900/90 dark:text-zinc-400">
                {{ __('No completed surveys in this date range.') }}
            </div>
        </div>
    </section>

    <section class="min-w-0 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <header class="mb-4">
            <flux:heading size="md">{{ __('Average patient rating') }}</flux:heading>
            <flux:text size="sm" class="mt-1">{{ __('Daily average rating during :dateRange.', ['dateRange' => $dateRange]) }}</flux:text>
        </header>

        <div wire:ignore x-data="chartComponent(@js($ratingTrend), 'line')"
            x-on:charts-update.window="updateChart($event.detail.ratingTrend)" class="relative h-64">
            <canvas x-ref="canvas" role="img" aria-label="{{ __('Average patient rating per day') }}"></canvas>
            <div x-cloak x-show="!hasData"
                class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/90 px-6 text-center text-sm text-zinc-500 dark:bg-zinc-900/90 dark:text-zinc-400">
                {{ __('No ratings in this date range.') }}
            </div>
        </div>
    </section>

    {{-- Template Ranking --}}
    @role('admin')
    <section class="min-w-0 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <header class="mb-5">
            <flux:heading size="md">{{ __('Most Answered Templates') }}</flux:heading>
            <flux:text size="sm" class="mt-1">{{ __('Completed responses during :dateRange.', ['dateRange' => $dateRange]) }}</flux:text>
        </header>

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
                                style="width: {{ ($template['total'] / $maxTemplate) * 100 }}%; background: linear-gradient(90deg, var(--color-brand-300), var(--color-brand-500));">
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
    </section>

    {{-- Insurer Breakdown --}}
    <section class="min-w-0 rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <header class="mb-5">
            <flux:heading size="md">{{ __('Surveys by Insurer') }}</flux:heading>
            <flux:text size="sm" class="mt-1">{{ __('Completed surveys during :dateRange.', ['dateRange' => $dateRange]) }}</flux:text>
        </header>

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
                                style="width: {{ ($insurer['total'] / $maxInsurer) * 100 }}%; background: linear-gradient(90deg, #818cf8, #6366f1);">
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
    </section>
    @endrole


    @script
    <script>
        Alpine.data('chartComponent', (incomingData, chartType) => {
            let chart;

            return {
                hasData: incomingData.hasData,

                init() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    const isBarChart = chartType === 'bar';
                    const chartColor = isBarChart ? 'rgb(79, 70, 229)' : 'rgb(13, 148, 136)';

                    chart = new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: [...incomingData.labels],
                            datasets: [{
                                label: incomingData.label,
                                data: [...incomingData.data],
                                backgroundColor: isBarChart ? 'rgba(79, 70, 229, 0.68)' : 'rgba(13, 148, 136, 0.12)',
                                borderColor: chartColor,
                                borderWidth: isBarChart ? 0 : 2,
                                borderRadius: isBarChart ? 4 : 0,
                                fill: !isBarChart,
                                tension: 0.3,
                                pointRadius: isBarChart ? 0 : 2,
                                pointHoverRadius: isBarChart ? 0 : 5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: { intersect: false, mode: 'index' }
                            },
                            interaction: { intersect: false, mode: 'index' },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { maxTicksLimit: 8, maxRotation: 0 }
                                },
                                y: {
                                    beginAtZero: true,
                                    ...(isBarChart ? { ticks: { precision: 0 } } : {
                                        max: 5,
                                        ticks: { stepSize: 1 }
                                    })
                                }
                            }
                        },
                    });
                },
                updateChart(value) {
                    if (!chart || !value) {
                        return;
                    }

                    this.hasData = value.hasData;
                    chart.data.labels = [...value.labels];
                    chart.data.datasets[0].label = value.label;
                    chart.data.datasets[0].data = [...value.data];
                    chart.update();
                }
            };
        });
    </script>
    @endscript
</div>