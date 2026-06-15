<flux:modal name="status-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('¿Cambiar estado de la plantilla?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('Vas a modificar la disponibilidad de:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedTemplateTitle }}</strong>.<br>
                {{ __('Si la desactivas, los pacientes no podrán responderla.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="toggleStatus" variant="primary">
                {{ __('Confirmar') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

<flux:modal name="delete-modal" class="max-w-md">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">
                {{ __('¿Eliminar plantilla de encuesta?') }}</flux:heading>
            <flux:text class="mt-2">
                {{ __('¿Estás seguro de que deseas eliminar:') }} <strong
                    class="text-zinc-800 dark:text-zinc-200">{{ $selectedTemplateTitle }}</strong>?<br>
                {{ __('Esta acción no se puede deshacer si la plantilla es removida del sistema.') }}
            </flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancelar') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="deleteTemplate" variant="danger">
                {{ __('Eliminar Permanentemente') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
