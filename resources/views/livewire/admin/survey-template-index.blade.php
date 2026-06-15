<div class="space-y-6 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <flux:heading size="xl">{{ __('Plantillas de Encuestas') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Administra los cuestionarios clínicos dinámicos y sus configuraciones.') }}
            </flux:text>
        </div>

        {{-- Botón con disparo controlado --}}
        <flux:button wire:click="openCreateFlyout" variant="primary" icon="plus">
            {{ __('Nueva Plantilla') }}
        </flux:button>
    </div>

    <flux:separator />

    @include('livewire.admin.partials._templates-table')
    @include('livewire.admin.partials._templates-modals')
    @include('livewire.admin.partials._templates-flyout')
    @include('livewire.admin.partials._templates-view-flyout')
</div>
