<?php

return [

    'mode' => 'saas',

    'saas' => [
        'tenant_model' => null, // set to App\Models\Tenant::class when you create it

        'db_prefix' => '{{PROJECT_NAME}}_',

        'central_domains' => [
            'localhost',
            '127.0.0.1',
        ],
    ],

];
