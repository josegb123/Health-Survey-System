<div class="space-y-6 p-6 max-w-4xl">
    <div>
        <flux:heading size="xl">{{ __('System Settings') }}</flux:heading>
        <flux:text class="mt-1">
            {{ __('Manage general parameters, security keys and global goals.') }}
        </flux:text>
    </div>

    <flux:separator />

    <form wire:submit="saveSettings" class="space-y-6">

        {{-- SECTION 1: Organization Data --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Institutional Information') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="company_name" label="{{ __('Company / Clinic Name') }}" />
                <flux:input wire:model="company_dni" label="{{ __('Tax ID / DNI') }}" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="entity_type" label="{{ __('Entity ID Type') }}" :placeholder="__('NI')"
                    hint="{{ __('e.g. NI for NIT, CC for ID, etc.') }}" />
                <flux:input type="number" wire:model="registry_type" label="{{ __('Registry Type') }}" min="1"
                    hint="{{ __('Record type identifier for the ministry file.') }}" />
            </div>
        </div>

        {{-- SECTION 1B: Default Survey Template --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Default Survey Template') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-500">
                {{ __('Select the template that will be used as the default for all reports: survey exports (Excel), statistics (PDF), and the Ministry of Health report. Only completed surveys using this template will be included.') }}
            </flux:text>
            <flux:select wire:model="default_survey_template_id" label="{{ __('Template') }}">
                <option value="">-- {{ __('Select a template') }} --</option>
                @foreach (\App\Models\SurveyTemplate::withCount('questions')->latest()->get() as $template)
                    <option value="{{ $template->id }}">
                        #{{ $template->id }} — {{ $template->title }} ({{ $template->questions_count }} {{ __('questions') }})
                    </option>
                @endforeach
            </flux:select>
        </div>

        {{-- SECTION 2: Preferences & Metrics --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Preferences & Metrics') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <flux:select wire:model="theme" label="{{ __('Default Theme') }}">
                        <option value="light">{{ __('Light') }}</option>
                        <option value="dark">{{ __('Dark') }}</option>
                        <option value="system">{{ __('System') }}</option>
                    </flux:select>
                </div>
                <div>
                    <flux:select wire:model="language" label="{{ __('Language') }}">
                        <option value="es">{{ __('Spanish') }}</option>
                        <option value="en">{{ __('English') }}</option>
                    </flux:select>
                </div>
                <div>
                    <flux:input type="number" wire:model="survey_monthly_goal"
                        label="{{ __('Monthly Survey Goal') }}" min="1" />
                </div>
            </div>
        </div>

        {{-- SECTION 3: Mail Settings --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Outgoing Mail Service') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="mail_from_address" type="email" label="{{ __('From Address') }}"
                    :placeholder="__('no-reply@clinic.com')" />
                <flux:input wire:model="mail_from_name" label="{{ __('From Name') }}"
                    :placeholder="__('Clinic System')" />
            </div>
        </div>

        {{-- SECTION 4: Security & Access Control --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
            <flux:heading size="md">{{ __('Security & Restrictions') }}</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input type="number" wire:model="rate_limit_requests"
                    label="{{ __('Rate Limit') }}" min="1"
                    hint="{{ __('Maximum requests per minute per IP.') }}" />
                <flux:input type="number" wire:model="session_timeout_minutes"
                    label="{{ __('Session Timeout (Minutes)') }}" min="1" />
            </div>

            <flux:separator class="my-2" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model="turnstile_site_key" label="{{ __('Cloudflare Turnstile Site Key') }}" />
                <flux:input wire:model="turnstile_secret_key" type="password"
                    label="{{ __('Cloudflare Turnstile Secret Key') }}" viewable />
            </div>
        </div>

        {{-- SECTION 5: Application State --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-3">
            <flux:heading size="md">{{ __('Server Status') }}</flux:heading>
            <div class="flex flex-col gap-1">
                <flux:checkbox wire:model="is_maintenance_mode" label="{{ __('Enable Maintenance Mode') }}" />
                <flux:text size="sm" class="text-zinc-400 pl-6">
                    {{ __('If enabled, public access to surveys will be temporarily disabled.') }}
                </flux:text>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex justify-end gap-2 pt-2">
            <flux:button type="submit" variant="primary" class="px-6">
                {{ __('Save Global Settings') }}
            </flux:button>
        </div>
    </form>

    {{-- SECTION 6: Data Cleanup --}}
    <div class="p-6 bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-900/50 rounded-xl space-y-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-full">
                <flux:icon name="trash" variant="outline" class="h-5 w-5" />
            </div>
            <div>
                <flux:heading size="md">{{ __('Old Surveys Cleanup') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Permanently delete all completed surveys, their answers, digital signatures, and orphan patients that are older than 6 months.') }}
                </flux:text>
            </div>
        </div>

        <flux:separator />

        @if ($purgeStep === 0)
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 text-sm text-zinc-500">
                    <flux:icon name="clock" variant="outline" class="h-4 w-4 text-zinc-400" />
                    @if ($surveys_purge_last_run)
                        {{ __('Last cleanup:') }} {{ \Carbon\Carbon::parse($surveys_purge_last_run)->format('d/m/Y H:i') }}
                    @else
                        {{ __('No previous cleanup recorded.') }}
                    @endif
                </div>
                <flux:button variant="danger" wire:click="startPurge">
                    {{ __('Clean Old Data') }}
                </flux:button>
            </div>
        @elseif ($purgeStep === 1)
            <div class="space-y-4">
                <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="exclamation-triangle" variant="outline" class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-red-700 dark:text-red-300">
                            <p class="font-semibold mb-1">{{ __('Warning: This action is irreversible') }}</p>
                            <p>{{ __('This will permanently delete all surveys, answers, digital signatures, and orphan patients that have been in the system for more than 6 months. This data cannot be recovered.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="cancelPurge">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="nextPurgeStep">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            </div>
        @elseif ($purgeStep === 2)
            <div class="space-y-4">
                <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="shield-exclamation" variant="outline" class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-red-700 dark:text-red-300 space-y-3">
                            <p class="font-semibold">{{ __('Final Confirmation') }}</p>
                            <p>{{ __('To proceed, type the following text exactly:') }}</p>
                            <p class="font-mono font-bold text-base bg-red-100 dark:bg-red-900/40 px-3 py-1.5 rounded inline-block">
                                {{ __('DELETE ALL') }}
                            </p>
                            <div>
                                <flux:input
                                    wire:model="confirmText"
                                    placeholder="{{ __('Type the confirmation text here') }}"
                                    :error="$errors->first('confirmText')"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="cancelPurge">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="executePurge">
                        {{ __('Permanently Delete') }}
                    </flux:button>
                </div>
            </div>
        @elseif ($purgeStep === 3)
            <div class="space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="check-circle" variant="outline" class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p class="font-semibold">{{ __('Cleanup Result') }}</p>
                            <p>{{ $purgeResult }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="primary" wire:click="cancelPurge">
                        {{ __('Done') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    {{-- SECTION 7: Delete All Signatures --}}
    <div class="p-6 bg-white dark:bg-zinc-900 border border-amber-200 dark:border-amber-900/50 rounded-xl space-y-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-full">
                <flux:icon name="pencil-square" variant="outline" class="h-5 w-5" />
            </div>
            <div>
                <flux:heading size="md">{{ __('Delete All Signatures') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Permanently delete all signature image files stored on disk. The signature_path references in surveys will remain but the files will no longer exist.') }}
                </flux:text>
            </div>
        </div>

        <flux:separator />

        @if ($signatureStep === 0)
            <div class="flex justify-end">
                <flux:button variant="danger" wire:click="startSignatureDelete">
                    {{ __('Delete All Signatures') }}
                </flux:button>
            </div>
        @elseif ($signatureStep === 1)
            <div class="space-y-4">
                <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="exclamation-triangle" variant="outline" class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-amber-700 dark:text-amber-300 space-y-3">
                            <p class="font-semibold">{{ __('Final Confirmation') }}</p>
                            <p>{{ __('To proceed, type the following text exactly:') }}</p>
                            <p class="font-mono font-bold text-base bg-amber-100 dark:bg-amber-900/40 px-3 py-1.5 rounded inline-block">
                                {{ __('DELETE ALL') }}
                            </p>
                            <div>
                                <flux:input
                                    wire:model="signatureConfirmText"
                                    placeholder="{{ __('Type the confirmation text here') }}"
                                    :error="$errors->first('signatureConfirmText')"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="cancelSignatureDelete">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="confirmSignatureDelete">
                        {{ __('Permanently Delete') }}
                    </flux:button>
                </div>
            </div>
        @elseif ($signatureStep === 2)
            <div class="space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="check-circle" variant="outline" class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p class="font-semibold">{{ __('Result') }}</p>
                            <p>{{ $signatureResult }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="primary" wire:click="cancelSignatureDelete">
                        {{ __('Done') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    {{-- SECTION 8: Reset Database --}}
    <div class="p-6 bg-white dark:bg-zinc-900 border border-red-200 dark:border-red-900/50 rounded-xl space-y-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-full">
                <flux:icon name="arrow-path" variant="outline" class="h-5 w-5" />
            </div>
            <div>
                <flux:heading size="md">{{ __('Reset Database') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Permanently delete ALL patients, surveys, survey answers, and signature files. Users and permissions are preserved. This action cannot be undone.') }}
                </flux:text>
            </div>
        </div>

        <flux:separator />

        @if ($resetStep === 0)
            <div class="flex justify-end">
                <flux:button variant="danger" wire:click="startReset">
                    {{ __('Reset Database') }}
                </flux:button>
            </div>
        @elseif ($resetStep === 1)
            <div class="space-y-4">
                <div class="p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="exclamation-triangle" variant="outline" class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-red-700 dark:text-red-300 space-y-3">
                            <p class="font-semibold">{{ __('Warning: This action is irreversible') }}</p>
                            <p>{{ __('This will permanently delete ALL patients, surveys, survey answers, and signature files from the system. Users and configuration will be preserved. This data cannot be recovered.') }}</p>
                            <p>{{ __('To proceed, type the following text exactly:') }}</p>
                            <p class="font-mono font-bold text-base bg-red-100 dark:bg-red-900/40 px-3 py-1.5 rounded inline-block">
                                {{ __('DELETE ALL') }}
                            </p>
                            <div>
                                <flux:input
                                    wire:model="resetConfirmText"
                                    placeholder="{{ __('Type the confirmation text here') }}"
                                    :error="$errors->first('resetConfirmText')"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="cancelReset">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="danger" wire:click="confirmReset">
                        {{ __('Permanently Delete All Data') }}
                    </flux:button>
                </div>
            </div>
        @elseif ($resetStep === 2)
            <div class="space-y-4">
                <div class="p-4 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900/50 rounded-lg">
                    <div class="flex items-start gap-3">
                        <flux:icon name="check-circle" variant="outline" class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 shrink-0" />
                        <div class="text-sm text-green-700 dark:text-green-300">
                            <p class="font-semibold">{{ __('Result') }}</p>
                            <p>{{ $resetResult }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button variant="primary" wire:click="cancelReset">
                        {{ __('Done') }}
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    {{-- SECTION 9: Export / Import Settings --}}
    <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-4">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full">
                <flux:icon name="arrow-down-tray" variant="outline" class="h-5 w-5" />
            </div>
            <div>
                <flux:heading size="md">{{ __('Export / Import Settings') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Export the current system settings and ministry report configuration as a JSON file, or import a previously exported configuration.') }}
                </flux:text>
            </div>
        </div>

        <flux:separator />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Export --}}
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="arrow-up-tray" variant="outline" class="h-4 w-4 text-zinc-500" />
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Export') }}</span>
                </div>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Download a JSON file with all system and ministry report settings.') }}
                </flux:text>
                <flux:button variant="primary" wire:click="exportSettings" class="w-full">
                    {{ __('Download Configuration') }}
                </flux:button>
            </div>

            {{-- Import --}}
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/30 border border-zinc-200 dark:border-zinc-700 rounded-lg space-y-3">
                <div class="flex items-center gap-2">
                    <flux:icon name="arrow-down-tray" variant="outline" class="h-4 w-4 text-zinc-500" />
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Import') }}</span>
                </div>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Upload a JSON configuration file. This will overwrite current settings.') }}
                </flux:text>
                <div class="space-y-2">
                    <input
                        type="file"
                        wire:model="importFile"
                        accept=".json"
                        class="block w-full text-sm text-zinc-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950/40 dark:file:text-blue-300 dark:hover:file:bg-blue-950/60 file:cursor-pointer"
                    />
                    @error('importFile')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <flux:button
                    variant="primary"
                    wire:click="importSettings"
                    wire:confirm="{{ __('This will overwrite your current configuration. Are you sure?') }}"
                    :disabled="! $importFile"
                    class="w-full"
                >
                    {{ __('Import Configuration') }}
                </flux:button>
            </div>
        </div>
    </div>
</div>
