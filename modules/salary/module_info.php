<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Salary Module Information
 */
return [
    'name' => 'Salary Management',
    'description' => 'Salary Management module for staff salary handling with initial salary and advance salary features',
    'version' => '1.0.0',
    'author' => 'FMS System',
    'requires_at_least' => '2.3.*',
    'category' => 'HR',
    'menu' => [
        'sidebar' => [
            'salary' => [
                'name' => 'Salary',
                'icon' => 'fa fa-money',
                'position' => 35,
                'children' => [
                    'salary_dashboard' => [
                        'name' => 'Dashboard',
                        'href' => 'admin/salary',
                        'position' => 1
                    ],
                    'salary_staff' => [
                        'name' => 'Staff Salary',
                        'href' => 'admin/salary/staff',
                        'position' => 5
                    ],
                    'salary_advance' => [
                        'name' => 'Advance Salary',
                        'href' => 'admin/salary/advance',
                        'position' => 10
                    ],
                    'salary_reports' => [
                        'name' => 'Reports',
                        'href' => 'admin/salary/reports',
                        'position' => 15
                    ],
                    'salary_settings' => [
                        'name' => 'Settings',
                        'href' => 'admin/salary/settings',
                        'position' => 20,
                        'admin_only' => true
                    ]
                ]
            ]
        ]
    ],
    'permissions' => [
        'salary' => [
            'view' => 'View Salary',
            'create' => 'Create Salary Records',
            'edit' => 'Edit Salary Records',
            'delete' => 'Delete Salary Records'
        ]
    ],
    'hooks' => [
        'admin_init' => [
            'salary_permissions',
            'salary_module_init_menu_items'
        ],
        'app_admin_head' => 'salary_add_head_components',
        'app_admin_footer' => 'salary_add_footer_components'
    ],
    'files' => [
        'helpers' => ['salary_helper.php'],
        'models' => ['Salary_model.php'],
        'controllers' => ['Salary.php'],
        'views' => [
            'dashboard.php',
            'staff.php',
            'staff_salary.php',
            'advance.php',
            'add_advance.php',
            'edit_advance.php',
            'reports.php',
            'settings.php',
            'tables/staff_salary.php',
            'tables/advance_salary.php'
        ],
        'assets' => [
            'css/salary.css',
            'js/salary.js'
        ],
        'language' => [
            'english/salary_lang.php'
        ]
    ]
];