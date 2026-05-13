<?php

use App\AppProvider;
use Innertia\InnertiaServiceProvider;

return [
    // InnertiaServiceProvider must be first — it configures JWT, auth, and (in saas mode) tenancy
    // before other providers read those configs.
    InnertiaServiceProvider::class,
    AppProvider::class,
];
