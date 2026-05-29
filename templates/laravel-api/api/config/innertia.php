<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Mode
    |--------------------------------------------------------------------------
    |
    | 'app'  — single-tenant. Auth via JWT. Settings globales.
    | 'saas' — multi-tenant. Auth via JWT. Settings por tenant.
    | 'api'  — producto API interno. Auth via API keys. Sin usuarios/tenants.
    |
    */

    'mode' => 'api',

    /*
    |--------------------------------------------------------------------------
    | API Product Mode
    |--------------------------------------------------------------------------
    |
    | key_prefix  — Prefijo de las API keys generadas (e.g. 'cog_', 'bil_').
    |               Define uno único por producto para distinguir keys entre servicios.
    | key_header  — Header HTTP usado para pasar la API key. Lo lee el middleware
    |               'verify.api.key', que inyecta organization + api_key al request.
    |
    | El modo api no maneja permisos en las keys: son identidad de la organización.
    | Proteger rutas de negocio:
    |   Route::middleware(\Innertia\Api\Routes::privateMiddleware())->group(...)
    |
    */

    'api' => [
        'key_prefix'  => env('API_KEY_PREFIX', 'api_'),
        'key_header'  => 'X-Api-Key',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'ttl' => 60, // minutos — null para no expirar
    ],

    /*
    |--------------------------------------------------------------------------
    | Backoffice (Olimpo)
    |--------------------------------------------------------------------------
    |
    | Rutas internas de administración expuestas bajo /olimpo.
    | Protegidas por olimpo.auth middleware.
    |
    */

    'backoffice' => [
        'prefix'     => env('BACKOFFICE_PREFIX', 'backoffice'),
        'middleware' => [],
        'enabled'    => true,

        'users' => [
            'allow_delete' => false,
        ],
    ],

];
