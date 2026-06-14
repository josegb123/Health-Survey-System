<flux:modal name="user-form-modal"
    class="md:w-96 backdrop:backdrop-blur-md backdrop:bg-zinc-950/20 dark:backdrop:bg-black/40">
    <div class="space-y-6">
        {{-- Cabecera Dinámica del Formulario --}}
        <div>
            <flux:heading size="lg">
                {{ $editingUser ? __('Update User Profile') : __('Create New User') }}
            </flux:heading>
            <flux:text class="mt-2">
                {{ $editingUser ? __('Make changes to the personal details.') : __('Fill in the details to register a new user.') }}
            </flux:text>
        </div>



        {{-- Formulario Reactivo --}}
        <form class="space-y-4" wire:submit="saveUser">

            <flux:input wire:model="name" :label="__('Name')" :placeholder="__('Your name')" />

            <flux:input type="email" wire:model="email" :label="__('Email')" :placeholder="__('Your email')" />

            <flux:input type="password" wire:model="password" :label="__('Password')" :placeholder="__('Your password')"
                :description="$editingUser ? __('Leave blank to keep current password.') : null" />

            <flux:select wire:model="role" :label="__('Role')" :placeholder="__('Choose a role...')">
                <flux:select.option value="admin">{{ __('Administrator') }}</flux:select.option>
                <flux:select.option value="user">{{ __('Standard User') }}</flux:select.option>
            </flux:select>

            {{-- Acciones del Formulario --}}
            <div class="flex pt-2">
                <flux:spacer />

                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ $editingUser ? __('Save Changes') : __('Create User') }}</span>
                    <span wire:loading>{{ __('Processing...') }}</span>
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
