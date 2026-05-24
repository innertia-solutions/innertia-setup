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
    | Apps / Contextos de acceso
    |--------------------------------------------------------------------------
    */

    'apps' => [
        'backoffice' => 'Administración',
    ],

    /*
    |--------------------------------------------------------------------------
    | SaaS / Tenancy Settings
    |--------------------------------------------------------------------------
    |
    | Only relevant when mode = 'saas'. Uncomment and configure if switching.
    |
    */

    // 'saas' => [
    //     'tenant_model' => \Innertia\Saas\Models\Tenant::class,
    //     'db_strategy'  => 'single',   // 'single' | 'multi'
    //     // 'db_prefix'  => '{{PROJECT_NAME}}_',  // only for db_strategy = 'multi'
    //     'central_domains' => ['localhost', '127.0.0.1'],
    // ],

    /*
    |--------------------------------------------------------------------------
    | Organizations (opt-in second-level scoping)
    |--------------------------------------------------------------------------
    |
    | When enabled, adds an Organization layer on top of the Tenant layer (or
    | directly under the app, in single-tenant mode). Useful when you need to
    | separate data between business units within the same tenant or app —
    | e.g. a consulting firm managing multiple clients, a holding with
    | multiple subsidiaries, or logical isolation between departments.
    |
    | Setup:
    |   1. Set 'enabled' => true and list your domain tables in 'tables'
    |   2. php artisan innertia:organization:install
    |   3. php artisan migrate
    |   4. Add `use HasOrganization` to scoped models
    |   5. Add 'organization.resolve' + 'organization.require' middleware to protected routes
    |   6. Client sends header: X-Organization: <slug>
    |
    | Forcibly inactive in api mode regardless of setting. Off by default.
    | Full guide: vendor/innertia-solutions/laravel-innertia/docs/organizations.md
    |
    */

    // 'organizations' => [
    //     'enabled'    => false,
    //     'tables'     => [
    //         // 'documents',
    //         // 'projects',
    //         // 'invoices',
    //     ],
    //     'column'     => 'organization_id',
    //     'with_index' => true,
    //     // 'model'   => \App\Domains\Organizations\Models\Organization::class,
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
