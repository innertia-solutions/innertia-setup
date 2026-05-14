<?php

use App\AppProvider;
use Innertia\InnertiaSaasProvider;

return [
    // InnertiaSaasProvider must be first — it configures JWT, auth, and tenancy
    // before other providers read those configs.
    InnertiaSaasProvider::class,
    AppProvider::class,
];
