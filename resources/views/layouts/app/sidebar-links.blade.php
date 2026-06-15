@php
    $platformMenu = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active_pattern' => 'dashboard*', // Activo en dashboard y subrutas
            'icon' => 'home',
            'navigate' => true,
        ],
        [
            'label' => 'Usuarios',
            'route' => 'users.index',
            'active_pattern' => 'users.*',
            'icon' => 'user',
            'navigate' => true,
        ],
        [
            'label' => 'Encuestas',
            'route' => 'admin.survey-templates.index',
            'active_pattern' => 'admin.survey-templates.*',
            'icon' => 'document-text',
            'navigate' => true,
        ],
    ];
@endphp

<x-sidebar-group heading="Platform" :items="$platformMenu" />
