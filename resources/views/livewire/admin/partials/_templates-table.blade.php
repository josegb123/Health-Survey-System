<flux:table :paginate="$templates">
    <flux:table.columns>
        <flux:table.column>{{ __('Survey Title') }}</flux:table.column>
        <flux:table.column class="text-center">{{ __('Questions') }}</flux:table.column>
        <flux:table.column>{{ __('Status') }}</flux:table.column>
        <flux:table.column>{{ __('Creation Date') }}</flux:table.column>
        <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($templates as $template)
            <flux:table.row :key="$template->id">
                <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                    {{ $template->title }}
                </flux:table.cell>

                <flux:table.cell class="text-center">
                    <flux:badge size="sm" color="zinc" inset="top bottom">
                        {{ $template->questions_count }}
                    </flux:badge>
                </flux:table.cell>

                <flux:table.cell>
                    @if ($template->is_active)
                        <flux:badge size="sm" color="green" inset="top bottom">{{ __('Active') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="red" inset="top bottom">{{ __('Inactive') }}</flux:badge>
                    @endif
                </flux:table.cell>

                <flux:table.cell class="text-sm text-zinc-500">
                    {{ $template->created_at->format('d/m/Y H:i') }}
                </flux:table.cell>

                <flux:table.cell class="text-right">
                    <flux:dropdown>
                        <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />

                        <flux:menu>
                            <flux:menu.item wire:click="viewTemplate({{ $template->id }})" icon="eye">
                                {{ __('View Detail') }}
                            </flux:menu.item>

                            <flux:menu.item icon="arrow-down-tray" wire:click="exportTemplate({{ $template->id }})">
                                {{ __('Export JSON') }}
                            </flux:menu.item>

                            @if (auth()->user()->isAdmin())
                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="confirmToggleStatus({{ $template->id }}, '{{ addslashes($template->title) }}')"
                                    icon="arrow-path">
                                    {{ $template->is_active ? __('Deactivate') : __('Activate') }}
                                </flux:menu.item>

                                <flux:menu.separator />

                                <flux:menu.item
                                    wire:click="confirmDelete({{ $template->id }}, '{{ addslashes($template->title) }}')"
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
                <flux:table.cell colspan="5" class="text-center py-8 text-zinc-400 italic">
                    {{ __('No survey templates registered in the system.') }}
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
