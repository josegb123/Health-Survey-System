<flux:modal name="create-insurer-modal" class="max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Create Insurer') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Add a new healthcare provider (EPS) to the system.') }}</flux:text>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="name" label="{{ __('Name') }}" placeholder="EPS Sura" />
            <flux:select wire:model="type" label="{{ __('Type') }}">
                <option value="contributory">{{ __('Contributory') }}</option>
                <option value="subsidized">{{ __('Subsidized') }}</option>
            </flux:select>
            <flux:checkbox wire:model="is_active" label="{{ __('Active') }}" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="saveInsurer" variant="primary" icon="check">
                {{ __('Create Insurer') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="edit-insurer-modal" class="max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Edit Insurer') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Modify the healthcare provider information.') }}</flux:text>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="editName" label="{{ __('Name') }}" />
            <flux:select wire:model="editType" label="{{ __('Type') }}">
                <option value="contributory">{{ __('Contributory') }}</option>
                <option value="subsidized">{{ __('Subsidized') }}</option>
            </flux:select>
            <flux:checkbox wire:model="editIsActive" label="{{ __('Active') }}" />
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="updateInsurer" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="status-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Change insurer status?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('You are about to modify the status of:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedInsurerName }}</strong>.<br>
                {{ __('If deactivated, patients linked to this insurer will still be visible.') }}
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
                {{ __('Delete insurer?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Are you sure you want to delete:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedInsurerName }}</strong>?<br>
                {{ __('This action cannot be undone.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="deleteInsurer" variant="danger">
                {{ __('Permanently Delete') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="duplicate-name-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-amber-600 dark:text-amber-400">
                {{ __('Insurer name already exists') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('An insurer with the name') }}
                <strong class="text-zinc-800 dark:text-zinc-200">"{{ $duplicateName }}"</strong>
                {{ __('already exists in the system. Please choose a different name.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="primary">{{ __('Understood') }}</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>

<flux:modal name="import-modal" class="max-w-lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Import Insurers from JSON') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Select a JSON file with insurer data. Duplicates will be skipped.') }}
            </flux:text>
        </div>

        <div>
            <flux:input
                type="file"
                wire:model="importFile"
                accept=".json"
                label="{{ __('Select JSON file') }}"
            />
            <p class="mt-1 text-xs text-zinc-500">{{ __('JSON format (.json) only') }}</p>
            @error('importFile')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button
                wire:click="importInsurers"
                variant="primary"
                icon="arrow-up-tray"
                :disabled="! $importFile">
                {{ __('Import Insurers') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
