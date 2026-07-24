<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Insurers') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage healthcare providers (EPS) and their configurations.') }}</flux:text>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="flex gap-2">
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal()">
                    {{ __('New Insurer') }}
                </flux:button>
                <flux:button variant="outline" icon="arrow-up-tray" wire:click="showImportModal">
                    {{ __('Import JSON') }}
                </flux:button>
                <flux:button variant="outline" icon="arrow-down-tray" wire:click="exportInsurers()">
                    {{ __('Export JSON') }}
                </flux:button>
            </div>
        @endif
    </div>

    <flux:separator />

    @include('livewire.admin.partials._insurers-table')
    @include('livewire.admin.partials._insurers-modals')
</div>
