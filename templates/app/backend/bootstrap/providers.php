<?php

use App\AppProvider;
use Innertia\InnertiaAppProvider;

return [
    // InnertiaAppProvider must be first — it configures JWT and auth
    // before other providers read those configs.
    InnertiaAppProvider::class,
    AppProvider::class,
];
