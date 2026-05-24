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
    | Apps / Contextos de acceso
    |--------------------------------------------------------------------------
    |
    | Define los contextos de la aplicación. El Login UseCase valida que el
    | usuario tenga acceso al app antes de emitir el token JWT.
    | Agregar más contextos según los roles de la app (ej: technician, sales).
    |
    */

    'apps' => [
        'backoffice' => 'Administración',
    ],

    /*
    |--------------------------------------------------------------------------
    | SaaS / Tenancy Settings
    |--------------------------------------------------------------------------
    */

    'saas' => [
        // Extend this model in your app if you need extra tenant columns.
        // class Tenant extends \Innertia\Saas\Models\Tenant { ... }
        'tenant_model' => \Innertia\Saas\Models\Tenant::class,

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
    | Organizations (opt-in second-level scoping)
    |--------------------------------------------------------------------------
    |
    | When enabled, adds an Organization layer ON TOP of the existing Tenant
    | layer. Useful when a single tenant has multiple business units that
    | shouldn't see each other's data by default — e.g. a consulting firm
    | tenant with many client orgs inside, or a holding tenant with many
    | subsidiaries. Also enables scoped RBAC: same user can be admin in one
    | org and viewer in another within the same tenant.
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
