<?php

// api.public.php — rutas sin autenticación (SaaS mode).
// ResolveTenantFromHeader lee X-Tenant: {slug} y resuelve el tenant.

use Illuminate\Support\Facades\Route;
use Innertia\Saas\Http\Controllers\TenantController;
use Innertia\Saas\Middleware\ResolveTenantFromHeader;

Route::middleware(ResolveTenantFromHeader::class)->group(function () {

    // Estado del tenant (boot SSR del frontend): tenant + features + branding.
    Route::get('status', [TenantController::class, 'status']);

    // Autenticación del contexto backoffice (login, otp, 2fa, email, password, oauth).
    \Innertia\Auth\Routes::publicRoutes('backoffice/auth');

    // Rutas de auth propias del producto van aquí (mismo grupo).
    // Multi-contexto: \Innertia\Auth\Routes::publicRoutes('technician/auth');
});
