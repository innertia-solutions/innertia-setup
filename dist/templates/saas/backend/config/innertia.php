<?php

return [

    'mode' => 'saas',

    'saas' => [
        'tenant_model' => null, // set to App\Models\Tenant::class when you create it

        // 'single' — shared DB, tenant_id column on every table
        // 'multi'  — separate DB per tenant (requires db_prefix)
        'db_strategy' => 'single',

        'db_prefix' => '{{PROJECT_NAME}}_', // only used when db_strategy = 'multi'

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
    | These are fallback values. Override at runtime via Settings::set().
    | In saas mode, Settings are scoped per active tenant automatically.
    |
    */

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
