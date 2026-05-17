<?php

use App\Providers\AppServiceProvider;
use Innertia\InnertiaApiProvider;

return [
    // InnertiaApiProvider configura el modo 'api': clients, API keys, middleware apikey.
    InnertiaApiProvider::class,
    AppServiceProvider::class,
];
