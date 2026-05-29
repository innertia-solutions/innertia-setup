<?php

// stubs/saas/api.private.php — rutas con autenticación requerida (SaaS mode)
// Middleware stack: tenant.resolve → authenticate → tenant.require
// Publicado por innertia-kit via: php artisan vendor:publish --tag=innertia-routes

use Illuminate\Support\Facades\Route;
use Innertia\Auth\Http\Controllers\SocialSettingsController;
use Innertia\Auth\Middleware\Authenticate;
use Innertia\Notifications\Http\NotificationsController;
use Innertia\Platform\Http\Controllers\SubscriptionController;
use Innertia\Saas\Middleware\RequireTenant;
use Innertia\Saas\Middleware\ResolveTenantFromHeader;

// ── Rutas de sesión — solo requieren autenticación (sin tenant obligatorio) ──
// auth/me, refresh y logout se usan antes/después de seleccionar un tenant.
Route::middleware([ResolveTenantFromHeader::class, Authenticate::class])->group(function () {
    \Innertia\Auth\Routes::sessionRoutes();
});

// ── Rutas de negocio — requieren autenticación + tenant activo ───────────────
Route::middleware([ResolveTenantFromHeader::class, Authenticate::class, RequireTenant::class])->group(function () {

    // ── Suscripciones ─────────────────────────────────────────────────────────
    Route::prefix('subscriptions')->group(function () {
        Route::get   ('/',     [SubscriptionController::class, 'index']);
        Route::post  ('/',     [SubscriptionController::class, 'store']);
        Route::patch ('{id}',  [SubscriptionController::class, 'update']);
        Route::delete('{id}',  [SubscriptionController::class, 'destroy']);
    });

    // ── Notificaciones ────────────────────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get   ('/',          [NotificationsController::class, 'index']);
        Route::patch ('/read-all',  [NotificationsController::class, 'markAllRead']);
        Route::patch ('/{id}/read', [NotificationsController::class, 'markRead']);
        Route::delete('/',          [NotificationsController::class, 'destroyRead']);
        Route::delete('/{id}',      [NotificationsController::class, 'destroy']);
    });

    // ── Admin: configuración de proveedores sociales ──────────────────────────
    Route::prefix('admin/auth')->group(function () {
        Route::get('settings',            [SocialSettingsController::class, 'index']);
        Route::get('{provider}/settings', [SocialSettingsController::class, 'show'])
            ->where('provider', 'google|microsoft|github');
        Route::put('{provider}/settings', [SocialSettingsController::class, 'update'])
            ->where('provider', 'google|microsoft|github');
    });

    // ── Backoffice genérico (users/roles/permissions/sessions del paquete) ──────
    // Una línea monta todo. Customizar un controller: extiéndelo y pásalo a register().
    \Innertia\Backoffice\Routes::register();

    // ── Organizations + Teams (descomentar si el producto los usa) ──────────────
    // \Innertia\Platform\Organizations\Routes::register('backoffice/organizations');
    // \Innertia\Platform\Teams\Routes::register('backoffice/teams');

    // ── Files + Directories (descomentar si el producto gestiona archivos) ──────
    // \Innertia\Files\Routes::register();
    // \Innertia\Files\Directories\Routes::register();

    // ── Rutas de la aplicación ────────────────────────────────────────────────

});
