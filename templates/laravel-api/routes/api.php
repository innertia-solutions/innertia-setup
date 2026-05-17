<?php

/*
|--------------------------------------------------------------------------
| API Routes — modo api (innertia-solutions/laravel-innertia)
|--------------------------------------------------------------------------
|
| Autenticación: X-Api-Key header, no JWT.
|
| Rutas públicas (sin apikey):  routes/api.public.php
| Rutas privadas (con apikey):  routes/api.private.php
|
| Publicar stubs:
|   php artisan vendor:publish --tag=innertia-routes
|
*/

require base_path('routes/api.public.php');
require base_path('routes/api.private.php');
