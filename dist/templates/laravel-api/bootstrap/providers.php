<?php

use App\Providers\AppServiceProvider;
use Innertia\InnertiaAppProvider;

return [
    // InnertiaAppProvider must be first — it configures JWT and auth
    // before other providers read those configs.
    InnertiaAppProvider::class,
    AppServiceProvider::class,
];
