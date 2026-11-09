<?php

// Sidebar menu. `route` may be null (renders "#") until you create the page.
// `icon` is an SVG path (Heroicons outline, 24x24).
// `permission` is the ability required to see the entry; omit it for pages
// that everyone with panel access may open. Groups have no permission of their
// own and disappear when none of their children are visible.
return [
    [
        'label' => 'Dashboard',
        'route' => 'admin.dashboard',
        'icon' => 'M2.25 12l8.955-8.955a1.5 1.5 0 012.122 0L22.28 12M4.5 9.75V19.5a.75.75 0 00.75.75H9.75v-5.25h4.5v5.25h4.5a.75.75 0 00.75-.75V9.75',
    ],
    [
        'label' => 'Users',
        'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'children' => [
            ['label' => 'All users', 'route' => 'admin.users', 'permission' => 'users.view'],
            ['label' => 'Roles',     'route' => 'admin.roles', 'permission' => 'roles.view'],

        ],
    ],
    [
        'label' => 'Shop',
        'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z',
        'children' => [
            ['label' => 'Products',   'route' => 'admin.products',   'permission' => 'products.view'],
            ['label' => 'Categories', 'route' => 'admin.categories', 'permission' => 'categories.view'],
            ['label' => 'Brands',     'route' => 'admin.brands',     'permission' => 'brands.view'],
            ['label' => 'Inventory',  'route' => 'admin.inventory',  'permission' => 'inventory.view'],
            ['label' => 'Units',      'route' => 'admin.units',      'permission' => 'units.view'],
            ['label' => 'Orders',     'route' => 'admin.orders',     'permission' => 'orders.view'],
            ['label' => 'Customers',  'route' => 'admin.customers',  'permission' => 'customers.view'],
        ],
    ],
    [
        'label' => 'CMS',
        'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5A3.375 3.375 0 0010.125 2.25H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V14.25M8.25 2.25V6.75h4.5',
        'children' => [
            ['label' => 'Pages', 'route' => 'admin.cms.pages', 'permission' => 'cms.manage'],
            ['label' => 'Posts', 'route' => 'admin.cms.posts', 'permission' => 'cms.manage'],
            ['label' => 'Menus', 'route' => 'admin.cms.menus', 'permission' => 'cms.manage'],
        ],
    ],
    [
        'label' => 'Media',
        'route' => 'admin.media',
        'permission' => 'media.view',
        'icon' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
    ],
    [
        'label' => 'Reports',
        'route' => 'admin.reports',
        'permission' => 'reports.view',
        'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
    ],
    [
        'label' => 'Settings',
        'route' => 'admin.settings',
        'permission' => 'settings.view',
        'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
    ],
];
