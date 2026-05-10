<?php

return [

    'mode' => 'saas',

    'saas' => [
        'tenant_model' => null, // set to App\Models\Tenant::class when you create it

        // 'single' — shared DB, models use BelongsToTenant + TenantScope
        // 'multi'  — separate DB per tenant (requires db_prefix)
        'db_strategy' => 'single',

        'db_prefix' => '{{PROJECT_NAME}}_', // only used when db_strategy = 'multi'

        'central_domains' => [
            'localhost',
            '127.0.0.1',
        ],
    ],

];
