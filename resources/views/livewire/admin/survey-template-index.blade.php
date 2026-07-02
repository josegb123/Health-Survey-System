<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Survey Templates') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage dynamic clinical questionnaires and their settings.') }}
            </flux:text>
        </div>

        @if (auth()->user()->isAdmin())
            <flux:button wire:click="openCreateFlyout" variant="primary" icon="plus">
                {{ __('New Template') }}
            </flux:button>
        @endif
    </div>

    <flux:separator />

    @include('livewire.admin.partials._templates-table')
    @include('livewire.admin.partials._templates-modals')
    @include('livewire.admin.partials._templates-flyout')
    @include('livewire.admin.partials._templates-view-flyout')
</div>
