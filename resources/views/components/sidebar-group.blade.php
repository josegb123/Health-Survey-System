@props(['heading', 'items' => []])

<flux:sidebar.group :heading="__($heading)" {{ $attributes->merge(['class' => 'grid']) }}>
    @foreach ($items as $item)
        @php
            // Soporta tanto nombres de ruta como URLs directas
            $href = isset($item['route']) ? route($item['route']) : $item['href'] ?? '#';

            // Determina si está activo basado en el nombre de la ruta o un patrón
            $current = isset($item['active_pattern'])
                ? request()->routeIs($item['active_pattern'])
                : (isset($item['route'])
                    ? request()->routeIs($item['route'])
                    : false);
        @endphp

        <flux:sidebar.item :icon="$item['icon'] ?? 'circle'" :href="$href" :current="$current"
            :wire:navigate="$item['navigate'] ?? true">
            {{ __($item['label']) }}
        </flux:sidebar.item>
    @endforeach
</flux:sidebar.group>
