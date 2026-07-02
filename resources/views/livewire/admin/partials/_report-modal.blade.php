<flux:modal name="report-modal" class="max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Survey Reports') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Select the period and generate reports.') }}</flux:text>
        </div>

        <flux:separator />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <flux:select wire:model.live="reportPeriod" label="{{ __('Period') }}">
                <option value="monthly">{{ __('Monthly') }}</option>
                <option value="quarterly">{{ __('Quarterly') }}</option>
                <option value="yearly">{{ __('Yearly') }}</option>
            </flux:select>

            @if ($reportPeriod === 'monthly')
                <flux:select wire:model.live="reportMonth" label="{{ __('Month') }}">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endforeach
                </flux:select>
            @elseif ($reportPeriod === 'quarterly')
                <flux:select wire:model.live="reportQuarter" label="{{ __('Quarter') }}">
                    @foreach (range(1, 4) as $q)
                        <option value="{{ $q }}">{{ __('Q') }}{{ $q }}</option>
                    @endforeach
                </flux:select>
            @endif

            <flux:select wire:model.live="reportYear" label="{{ __('Year') }}">
                @foreach (range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <flux:input type="date" wire:model="reportStartDate" label="{{ __('Start Date') }}" />
            <flux:input type="date" wire:model="reportEndDate" label="{{ __('End Date') }}" />
        </div>

        <flux:separator />

        <div class="space-y-3">
            <flux:heading size="sm">{{ __('Available Reports') }}</flux:heading>

            <div class="grid grid-cols-1 gap-3">
                {{-- Report 1: Surveys --}}
                <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                            <flux:icon name="document-text" variant="outline" class="h-5 w-5" />
                        </div>
                        <div>
                            <span class="font-medium text-sm text-zinc-900 dark:text-white">{{ __('Export Surveys') }}</span>
                            <p class="text-xs text-zinc-400">{{ __('List of surveys with patient data and ratings.') }}</p>
                        </div>
                    </div>
                    <flux:button size="sm" variant="primary" wire:click="downloadSurveysReport" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="downloadSurveysReport">{{ __('PDF') }}</span>
                        <span wire:loading wire:target="downloadSurveysReport">{{ __('Generating...') }}</span>
                    </flux:button>
                </div>

                {{-- Report 2: Statistics --}}
                <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-lg">
                            <flux:icon name="chart-bar" variant="outline" class="h-5 w-5" />
                        </div>
                        <div>
                            <span class="font-medium text-sm text-zinc-900 dark:text-white">{{ __('Export Statistics') }}</span>
                            <p class="text-xs text-zinc-400">{{ __('Analytical report with metrics and breakdowns.') }}</p>
                        </div>
                    </div>
                    <flux:button size="sm" variant="primary" wire:click="downloadStatisticsReport" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="downloadStatisticsReport">{{ __('PDF') }}</span>
                        <span wire:loading wire:target="downloadStatisticsReport">{{ __('Generating...') }}</span>
                    </flux:button>
                </div>

                {{-- Report 3: Ministry of Health --}}
                <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 rounded-lg">
                            <flux:icon name="building-office" variant="outline" class="h-5 w-5" />
                        </div>
                        <div>
                            <span class="font-medium text-sm text-zinc-900 dark:text-white">{{ __('Export Ministry of Health') }}</span>
                            <p class="text-xs text-zinc-400">{{ __('Formatted TXT file for the Ministry of Health.') }}</p>
                        </div>
                    </div>
                    <flux:button size="sm" variant="primary" wire:click="downloadMinistryReport" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="downloadMinistryReport">{{ __('TXT') }}</span>
                        <span wire:loading wire:target="downloadMinistryReport">{{ __('Generating...') }}</span>
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Close') }}</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
