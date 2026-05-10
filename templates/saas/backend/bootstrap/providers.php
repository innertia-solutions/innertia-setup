<?php

use App\Providers\AppServiceProvider;
use Innertia\InnertiaServiceProvider;
use Stancl\Tenancy\TenancyServiceProvider;

return [
    // InnertiaServiceProvider must come before TenancyServiceProvider:
    // it sets config('tenancy.*') so stancl reads the correct bootstrappers on register().
    // Both packages are excluded from auto-discovery (see composer.json dont-discover).
    InnertiaServiceProvider::class,
    TenancyServiceProvider::class,
    AppServiceProvider::class,
];
