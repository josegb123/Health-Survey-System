<flux:table :paginate="$insurers">
    <flux:table.columns>
        <flux:table.column class="w-16">{{ __('ID') }}</flux:table.column>
        <flux:table.column>{{ __('Name') }}</flux:table.column>
        <flux:table.column>{{ __('Type') }}</flux:table.column>
        <flux:table.column class="text-center">{{ __('Patients') }}</flux:table.column>
        <flux:table.column>{{ __('Status') }}</flux:table.column>
        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($insurers as $insurer)
            <flux:table.row :key="$insurer->id">
                <flux:table.cell>
                    <span class="text-zinc-400 dark:text-zinc-500 text-xs font-mono">#{{ $insurer->id }}</span>
                </flux:table.cell>

                <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                    {{ $insurer->name }}
                </flux:table.cell>

                <flux:table.cell>
                    @if ($insurer->type === 'contributory')
                        <flux:badge size="sm" color="blue" inset="top bottom">{{ __('Contributory') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Subsidized') }}</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="text-center">
                    <flux:badge size="sm" color="zinc" inset="top bottom">
                        {{ $insurer->patients_count }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell>
                    @if ($insurer->is_active)
                        <flux:badge size="sm" color="green" inset="top bottom">{{ __('Active') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="red" inset="top bottom">{{ __('Inactive') }}</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="text-right">
                    <flux:dropdown>
                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />

                        <flux:menu>
                            <flux:menu.item wire:click="openEditModal({{ $insurer->id }})" icon="pencil">
                                {{ __('Edit') }}
                            </flux:menu.item>

                            @if (auth()->user()->isAdmin())
                                <flux:menu.item
                                    wire:click="confirmToggleStatus({{ $insurer->id }}, '{{ addslashes($insurer->name) }}')"
                                    icon="arrow-path">
                                    {{ $insurer->is_active ? __('Deactivate') : __('Activate') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="confirmDelete({{ $insurer->id }}, '{{ addslashes($insurer->name) }}')"
                                    icon="trash" variant="danger">
                                    {{ __('Delete') }}
                                </flux:menu.item>
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="text-center py-8 text-zinc-400 italic">
                    {{ __('No insurers registered in the system.') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
