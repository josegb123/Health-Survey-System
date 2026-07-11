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

<flux:modal name="import-modal" class="max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Import Template from JSON') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Select a JSON file previously exported from this system.') }}<br>
                {{ __('The template will be created with the title and questions contained in the file.') }}
            </flux:text>
        </div>

        <div>
            <flux:file.upload
                wire:model="importFile"
                accept=".json"
                multiple="false"
                label="{{ __('Select JSON file') }}"
                help="{{ __('JSON format (.json) only') }}"
            />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button
                wire:click="importTemplate"
                variant="primary"
                icon="arrow-up-tray"
                :disabled="! $importFile">
                {{ __('Import Template') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
