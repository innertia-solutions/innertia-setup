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
    | key_header  — Header HTTP usado para pasar la API key.
    |
    | available_permissions — Permisos que pueden asignarse a las API keys.
    |   Apunta a tu enum \App\Enums\ApiPermissions::class (recomendado)
    |   o define un array plano: ['perm.name' => 'Descripción'].
    |
    |   El enum es type-safe, se puede autocompletar en el IDE y lleva la
    |   descripción inline. Olimpo lo lee via GET /olimpo/api-keys/permissions.
    |
    | Proteger rutas:
    |   Route::middleware('apikey')->group(...)
    |   Route::middleware('apikey:permission.name')->get(...)
    |
    */

    'api' => [
        'key_prefix'  => env('API_KEY_PREFIX', 'api_'),
        'key_header'  => 'X-Api-Key',

        'available_permissions' => \App\Enums\ApiPermissions::class,
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
