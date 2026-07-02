@php
    $platformMenu = [
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active_pattern' => 'dashboard*',
            'icon' => 'home',
            'navigate' => true,
        ],
        [
            'label' => 'Surveys',
            'route' => 'admin.surveys.index',
            'active_pattern' => 'admin.surveys.*',
            'icon' => 'clipboard-document-list',
            'navigate' => true,
        ],
        [
            'label' => 'Templates',
            'route' => 'admin.survey-templates.index',
            'active_pattern' => 'admin.survey-templates.*',
            'icon' => 'document-text',
            'navigate' => true,
        ],
    ];

    if (auth()->user()->isAdmin()) {
        $platformMenu[] = [
            'label' => 'Users',
            'route' => 'users.index',
            'active_pattern' => 'users.*',
            'icon' => 'user',
            'navigate' => true,
        ];

        $platformMenu[] = [
            'label' => 'System Settings',
            'route' => 'admin.settings',
            'active_pattern' => 'admin.settings.*',
            'icon' => 'cog',
            'navigate' => true,
        ];
    }
@endphp

<x-sidebar-group heading="Platform" :items="$platformMenu" />
