<?php

// Every ability the admin panel can grant. PermissionSeeder writes these into
// the permissions table and the Roles screen renders them as a checkbox
// matrix, grouped by the part of the panel they unlock.
//
// The super role below is protected: its members bypass every check through
// the Gate::before hook registered in AppServiceProvider.
return [
    'super_role' => 'admin',

    'groups' => [
        'Catalog' => [
            'products.view' => 'View products',
            'products.manage' => 'Create, edit and delete products',
            'categories.view' => 'View categories',
            'categories.manage' => 'Create, edit and delete categories',
            'brands.view' => 'View brands',
            'brands.manage' => 'Create, edit and delete brands',
            'inventory.view' => 'View inventory and stock movements',
            'inventory.manage' => 'Adjust stock levels',
            'units.view' => 'View units',
            'units.manage' => 'Create, edit and delete units',
        ],
        'Sales' => [
            'orders.view' => 'View orders',
            'orders.manage' => 'Update order status and verify payments',
            'customers.view' => 'View customers',
            'customers.manage' => 'Edit and suspend customers',
            'reports.view' => 'View sales, product, customer and stock reports',
        ],
        'Marketing' => [
            'subscribers.view' => 'View newsletter subscribers and campaigns',
            'subscribers.manage' => 'Manage subscribers and send email campaigns',
        ],
        'Content' => [
            'cms.manage' => 'Create, edit and delete pages, posts and menus',
        ],
        'Media' => [
            'media.view' => 'Browse the media library',
            'media.manage' => 'Upload, move and delete media files',
        ],
        'System' => [
            'users.view' => 'View user accounts',
            'users.manage' => 'Create, edit and delete user accounts',
            'roles.view' => 'View roles',
            'roles.manage' => 'Create, edit and delete roles',
            'settings.view' => 'View business settings',
            'settings.manage' => 'Change business settings',
        ],
    ],
];
