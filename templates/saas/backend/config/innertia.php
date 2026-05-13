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

    'mode' => 'saas',

    /*
    |--------------------------------------------------------------------------
    | SaaS / Tenancy Settings
    |--------------------------------------------------------------------------
    */

    'saas' => [
        // Extend this model in your app if you need extra tenant columns.
        // class Tenant extends \Innertia\Models\Tenant { ... }
        'tenant_model' => \Innertia\Models\Tenant::class,

        // 'single' — all tenants share one database (tenant_id on every table)
        // 'multi'  — each tenant gets its own database
        'db_strategy' => 'single',

        // Only used when db_strategy = 'multi'. Tenant DB name: {prefix}{tenant_id}
        // 'db_prefix' => '{{PROJECT_NAME}}_',

        // Domains that host the central (landlord) application
        'central_domains' => [
            'localhost',
            '127.0.0.1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Defaults
    |--------------------------------------------------------------------------
    |
    | These are fallback values only. At runtime the auth layer reads from
    | the Settings system (database) so each tenant can override them without
    | a deployment. Set live values via Settings::set():
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
        \App\Domains\Users\Permissions\UserPermissions::class,
        \App\Domains\Users\Permissions\RolePermissions::class,
        \App\Domains\Users\Permissions\SystemPermissions::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail / Email Branding
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    |
    | disk    — storage disk for tenant export ZIPs (defaults to cloud disk).
    | handler — your TenantExport subclass. When set, Olimpo's backup endpoint
    |           automatically queues an export using this class.
    |
    */

    'exports' => [
        'disk'    => env('EXPORT_DISK', env('FILESYSTEM_CLOUD', 'local')),
        'handler' => null, // e.g. \App\Exports\ExportTenantData::class
    ],

    'mail' => [
        'logo_url'    => env('MAIL_LOGO_URL', null),
        'brand_color' => env('MAIL_BRAND_COLOR', '#6366f1'),
    ],

    'auth' => [
        /*
        |----------------------------------------------------------------------
        | User Model
        |----------------------------------------------------------------------
        |
        | The Eloquent model used for authentication. Innertia registers the
        | JWT api guard and providers automatically — you don't need to touch
        | config/auth.php. Override here if your User model path differs.
        |
        */

        'user_model' => \App\Domains\Users\Models\User::class,

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
