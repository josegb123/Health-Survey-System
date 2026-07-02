<div class="space-y-6 p-6">
    {{-- Cabecera de la Tabla: Título y Acciones Principales --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Users Management') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your application users, roles and permissions.') }}</flux:text>
        </div>

        <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
            {{ __('Add User') }}
        </flux:button>
    </div>

    {{-- Tabla de Datos Principal --}}
    <flux:table :paginate="$users">
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Enable') }}</flux:table.column>
            <flux:table.column>{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row :key="$user->id">
                    {{-- Información básica del Usuario --}}
                    <flux:table.cell class="font-medium">
                        <div class="flex flex-col">
                            <span>{{ $user->name }}</span>
                            <span class="text-xs text-gray-500 font-normal">{{ $user->email }}</span>
                        </div>
                    </flux:table.cell>

                    {{-- Flujo alternativo: Control de errores si el usuario no tiene roles --}}
                    <flux:table.cell>
                        @if ($user->roles->isNotEmpty())
                            <flux:badge size="sm" color="zinc" inset="top bottom">
                                {{ $user->roles->first()->name }}
                            </flux:badge>
                        @else
                            <span class="text-xs text-gray-400 italic">{{ __('No role assigned') }}</span>
                        @endif
                    </flux:table.cell>

                    {{-- Fecha de creación formateada --}}
                    <flux:table.cell class="text-gray-500 text-sm">
                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                    </flux:table.cell>

                    {{-- Botones de acción alineados a la derecha --}}
                    <flux:table.cell>
                        <div class="flex gap-x-2">
                            <flux:button size="sm" variant="subtle"
                                wire:click="openEditModal({{ $user->id }})" icon="pencil">
                                {{ __('Edit') }}
                            </flux:button>

                            @php
                                $cannotDelete = $user->id === auth()->id() || $user->isAdmin();
                            @endphp
                            <flux:button size="sm" variant="danger"
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="{{ $cannotDelete ? '' : __('Are you absolutely sure you want to delete :name? This action cannot be undone.', ['name' => $user->name]) }}"
                                icon="trash" :disabled="$cannotDelete">
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                {{-- Estado vacío estructuralmente correcto para HTML/Flux --}}
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-8 text-gray-400 italic">
                        {{ __('No users found in the database.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Inclusión del Modal --}}
    @include('livewire.users.__partials.user-modal')
</div>
