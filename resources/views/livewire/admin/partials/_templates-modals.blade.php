<flux:modal name="status-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Change template status?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('You are about to modify the availability of:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedTemplateTitle }}</strong>.<br>
                {{ __('If deactivated, patients will not be able to answer it.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="toggleStatus" variant="primary">
                {{ __('Confirm') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="delete-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">
                {{ __('Delete survey template?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Are you sure you want to delete:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedTemplateTitle }}</strong>?<br>
                {{ __('This action cannot be undone if the template is removed from the system.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="deleteTemplate" variant="danger">
                {{ __('Permanently Delete') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
