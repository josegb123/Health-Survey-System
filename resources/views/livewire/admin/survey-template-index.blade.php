<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Survey Templates') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage dynamic clinical questionnaires and their settings.') }}
            </flux:text>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="flex gap-2">
                <a href="{{ route('admin.survey-templates.create') }}">
                    <flux:button variant="primary" icon="plus">{{ __('New Template') }}</flux:button>
                </a>
                <flux:button variant="outline" icon="arrow-up-tray" wire:click="showImportModal()">
                    {{ __('Import JSON') }}
                </flux:button>
            </div>
        @endif
    </div>

    <flux:separator />

    @include('livewire.admin.partials._templates-table')
    @include('livewire.admin.partials._templates-modals')
    @include('livewire.admin.partials._templates-view-flyout')
</div>
