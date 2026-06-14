<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Menu token tidak ditampilkan pada sidebar Admin karena token akan
    | dikelola melalui endpoint autentikasi dan halaman akun masing-masing.
    |
    */

    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'Administration',
            'sort' => 100,
            'icon' => 'heroicon-o-key',
            'should_register_navigation' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Model
    |--------------------------------------------------------------------------
    */

    'models' => [
        'token' => [
            'enable_policy' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Route
    |--------------------------------------------------------------------------
    |
    | Dengan panel_prefix = true, resource API dari panel admin menggunakan:
    |
    | /api/admin/{resource}
    |
    */

    'route' => [
        'panel_prefix' => true,
        'use_resource_middlewares' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    */

    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    'login-rules' => [
        'email' => [
            'required',
            'email',
        ],
        'password' => [
            'required',
            'string',
        ],
    ],

    'login-middleware' => [
        'throttle:10,1',
    ],

    'logout-middleware' => [
        'auth:sanctum',
    ],

    /*
    |--------------------------------------------------------------------------
    | Spatie Permission Middleware
    |--------------------------------------------------------------------------
    |
    | Dinonaktifkan pada tahap awal karena pembatasan akses akan diterapkan
    | melalui custom handler, policy, dan query scope berdasarkan role.
    |
    */

    'use-spatie-permission-middleware' => false,
];