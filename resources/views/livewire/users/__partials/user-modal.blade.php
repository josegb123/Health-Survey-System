{{-- Regresamos al ancho md:w-96, añadimos scroll interno vertical si la pantalla es muy baja --}}
<flux:modal name="user-form-modal"
    class="md:w-96 max-h-[90vh] overflow-y-auto backdrop:backdrop-blur-md backdrop:bg-zinc-950/20 dark:backdrop:bg-black/40">
    <div class="space-y-6">
        {{-- Cabecera del Formulario --}}
        <div>
            <flux:heading size="lg">
                {{ $editingUser ? __('Update User Profile') : __('Create New User') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $editingUser ? __('Make changes to the personal details.') : __('Fill in the details to register a new user.') }}
            </flux:text>
        </div>

        {{-- Formulario Reactivo Lineal (Garantiza cero problemas de alineación) --}}
        <form class="space-y-4" wire:submit="saveUser">

            {{-- Campo Nombre --}}
            <flux:input wire:model="name" :label="__('Name')" :placeholder="__('Your name')" />

            {{-- Campo Email --}}
            <flux:input type="email" wire:model="email" :label="__('Email')" :placeholder="__('Your email')" />

            {{-- Contraseña Principal (Estado Alpine aislado) --}}
            <div x-data="{ showPassword: false }">
                <flux:input type="password" x-bind:type="showPassword ? 'text' : 'password'" wire:model="password"
                    :label="__('Password')" :placeholder="__('Your password')"
                    :description="$editingUser ? __('Leave blank to keep current password.') : null">
                    <x-slot name="iconTrailing">
                        <flux:button variant="subtle" size="sm" class="-mr-2"
                            x-on:click="showPassword = !showPassword">
                            <flux:icon icon="eye" variant="micro" x-show="!showPassword" />
                            <flux:icon icon="eye-slash" variant="micro" x-show="showPassword" x-cloak />
                        </flux:button>
                    </x-slot>
                </flux:input>
            </div>

            {{-- Confirmación de Contraseña (Estado Alpine aislado e independiente) --}}
            <div x-data="{ showConfirmPassword: false }">
                <flux:input type="password" x-bind:type="showConfirmPassword ? 'text' : 'password'"
                    wire:model="password_confirmation" :label="__('Confirm Password')"
                    :placeholder="__('Repeat password')">
                    <x-slot name="iconTrailing">
                        <flux:button variant="subtle" size="sm" class="-mr-2"
                            x-on:click="showConfirmPassword = !showConfirmPassword">
                            <flux:icon icon="eye" variant="micro" x-show="!showConfirmPassword" />
                            <flux:icon icon="eye-slash" variant="micro" x-show="showConfirmPassword" x-cloak />
                        </flux:button>
                    </x-slot>
                </flux:input>
            </div>

            {{-- Selección de Rol --}}
            <flux:select wire:model="role" :label="__('Role')" :placeholder="__('Choose a role...')">
                <flux:select.option value="admin">{{ __('Administrator') }}</flux:select.option>
                <flux:select.option value="user">{{ __('Standard User') }}</flux:select.option>
            </flux:select>

            {{-- Estado de la Cuenta --}}
            <div class="pt-2">
                <flux:checkbox wire:model="is_active" :label="__('Active Account')" />
            </div>

            {{-- Acciones del Formulario --}}
            <div class="flex pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $editingUser ? __('Save Changes') : __('Create User') }}</span>
                    <span wire:loading>{{ __('Processing...') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
