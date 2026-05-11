<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Mode
    |--------------------------------------------------------------------------
    |
    | 'app'  — single-tenant. Settings are global (tenant_id = null).
    | 'saas' — multi-tenant. Settings resolve per active tenant with fallback
    |          to platform level. Tenancy is configured automatically.
    |
    */

    'mode' => 'app',

    /*
    |--------------------------------------------------------------------------
    | SaaS / Tenancy Settings
    |--------------------------------------------------------------------------
    |
    | Only relevant when mode = 'saas'. Uncomment and configure if switching.
    |
    */

    // 'saas' => [
    //     'tenant_model' => \Innertia\Models\Tenant::class,
    //     'db_strategy'  => 'single',   // 'single' | 'multi'
    //     // 'db_prefix'  => '{{PROJECT_NAME}}_',  // only for db_strategy = 'multi'
    //     'central_domains' => ['localhost', '127.0.0.1'],
    // ],

    /*
    |--------------------------------------------------------------------------
    | Auth Defaults
    |--------------------------------------------------------------------------
    |
    | These are fallback values only. At runtime the auth layer reads from
    | the Settings system (database) so values can be changed without a
    | deployment. Set live values via Settings::set():
    |
    |   Settings::set('auth.otp.enabled', true);
    |   Settings::set('auth.email_verification.enabled', true);
    |   Settings::set('auth.sessions.restrict_concurrent', true);
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Define your application permissions grouped by category. Run:
    |   php artisan innertia:permissions          — create missing permissions
    |   php artisan innertia:permissions --prune  — also delete removed ones
    |
    */

    'permissions' => [
        [
            'category'       => 'system',
            'category_alias' => 'Sistema',
            'permissions'    => [
                'system.admin'           => 'Administración completa del sistema',
                'system.roles.manage'    => 'Administrar roles',
                'system.permissions.view'=> 'Ver permisos del sistema',
            ],
        ],
    ],

    'auth' => [
        'email_verification' => [
            'enabled' => false,
            'ttl'     => 60,    // minutes
        ],

        'otp' => [
            'enabled' => false,
            'ttl'     => 10,    // minutes
        ],

        '2fa' => [
            'enabled' => false,
        ],

        'sessions' => [
            'restrict_concurrent' => false,
        ],
    ],

];
