<d class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Analytics and performance monitoring for clinical surveys.') }}</flux:text>
        </div>

        <div>
            <flux:select wire:model.live="period" class="w-48">
                <option value="week">{{ __('Last 7 days') }}</option>
                <option value="month">{{ __('Last Month') }}</option>
                <option value="quarter">{{ __('Last Quarter') }}</option>
                <option value="year">{{ __('Last Year') }}</option>
            </flux:select>
        </div>
    </div>

    <flux:separator />

    <livewire:admin.dashboard.stats-cards :startDate="$startDate" :endDate="$endDate" />
    <livewire:admin.dashboard.charts-section :startDate="$startDate" :endDate="$endDate" :period="$period" />
    <livewire:admin.dashboard.recent-surveys-table :recentSurveys="$recentSurveys" />
</d>
