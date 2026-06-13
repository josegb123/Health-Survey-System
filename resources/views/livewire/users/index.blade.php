<div>
    <div class="py-3">
        <flux:button>Crear Usuario</flux:button>
    </div>
    <flux:table :paginate="$users" pagination:scroll-to="#users">
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Rol</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row>
                    <flux:table.cell>{{ $user->name }}</flux:table.cell>
                    <flux:table.cell>{{ $user->roles[0]->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="green" size="sm" inset="top bottom">{{ $user->created_at }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong" class="flex gap-x-2">
                        <flux:button variant="primary">Editar</flux:button>

                        <flux:button variant="danger">Eliminar</flux:button>

                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <p>sin usuarios en la base de datos</p>

                </flux:table.row>
            @endforelse

        </flux:table.rows>
    </flux:table>
</div>
