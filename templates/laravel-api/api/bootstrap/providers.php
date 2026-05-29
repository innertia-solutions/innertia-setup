<?php

use App\Providers\AppServiceProvider;
use Innertia\InnertiaApiProvider;

return [
    // InnertiaApiProvider configura el modo 'api': Organizations, ApiKeys, middleware verify.api.key.
    InnertiaApiProvider::class,
    AppServiceProvider::class,
];
