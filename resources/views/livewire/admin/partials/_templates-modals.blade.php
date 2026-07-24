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

<flux:modal name="delete-modal" class="max-w-lg">
    <div class="space-y-6">
        @if ($deleteStep === 1)
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">
                    {{ __('Delete survey template?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('You are about to delete:') }} <strong
                        class="text-zinc-800 dark:text-zinc-200">#{{ $selectedTemplateId }} {{ $selectedTemplateTitle }}</strong>.
                </flux:text>
            </div>

            @if ($deleteSurveyCount > 0)
                <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="exclamation-triangle" variant="outline" class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-red-700 dark:text-red-300">
                            <p class="font-semibold mb-1">{{ __('This template has :count survey(s) and associated data.', ['count' => $deleteSurveyCount]) }}</p>
                            <p>{{ __('Deleting it will permanently remove all surveys, answers, and digital signatures linked to this template.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button wire:click="cancelDelete" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="proceedToDeleteStep2" variant="danger">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            @else
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button wire:click="cancelDelete" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button wire:click="deleteTemplate" variant="danger">
                        {{ __('Permanently Delete') }}
                    </flux:button>
                </div>
            @endif

        @elseif ($deleteStep === 2)
            <div>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">
                    {{ __('Final Confirmation') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('This action is irreversible. All data associated with') }}
                    <strong class="text-zinc-800 dark:text-zinc-200">#{{ $selectedTemplateId }} {{ $selectedTemplateTitle }}</strong>
                    {{ __('will be permanently deleted.') }}
                </flux:text>
            </div>

            <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg space-y-3">
                <p class="text-sm text-red-700 dark:text-red-300">{{ __('To proceed, type the following text exactly:') }}</p>
                <p class="font-mono font-bold text-base bg-red-100 dark:bg-red-900/40 px-3 py-1.5 rounded inline-block text-red-700 dark:text-red-300">
                    {{ __('DELETE ALL') }}
                </p>
                <div>
                    <flux:input
                        wire:model="deleteConfirmText"
                        placeholder="{{ __('Type the confirmation text here') }}"
                        :error="$errors->first('deleteConfirmText')"
                    />
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="cancelDelete" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="deleteTemplate" variant="danger">
                    {{ __('Permanently Delete Everything') }}
                </flux:button>
            </div>
        @endif
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
            <flux:input
                type="file"
                wire:model="importFile"
                accept=".json"
                label="{{ __('Select JSON file') }}"
            />
            <p class="mt-1 text-xs text-zinc-500">{{ __('JSON format (.json) only') }}</p>
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

<flux:modal name="duplicate-name-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-amber-600 dark:text-amber-400">
                {{ __('Template name already exists') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('A template with the name') }}
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
