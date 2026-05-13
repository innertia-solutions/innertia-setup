<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Innertia auth + platform routes (innertia-solutions/laravel-kit)
|--------------------------------------------------------------------------
|
| Auth
|   POST   /auth/login
|   POST   /auth/otp/send            POST /auth/otp/verify
|   POST   /auth/2fa/verify
|   POST   /auth/email/verify/send   GET  /auth/email/verify
|   POST   /auth/password/change     POST /auth/password/set
|   GET    /auth/me                  POST /auth/refresh         (protected)
|   POST   /auth/logout                                         (protected)
|   POST   /auth/2fa/enable          POST /auth/2fa/disable     (protected)
|
| Social login (configure credentials via admin endpoints below)
|   GET    /auth/{provider}/redirect?app=   google | microsoft | github
|   GET    /auth/{provider}/callback
|
| Subscriptions                                                 (protected)
|   GET    /subscriptions
|   POST   /subscriptions
|   PATCH  /subscriptions/{id}
|   DELETE /subscriptions/{id}
|
| Admin — social provider settings                             (protected)
|   GET    /admin/auth/settings
|   GET    /admin/auth/{provider}/settings
|   PUT    /admin/auth/{provider}/settings
|
*/
require base_path('vendor/innertia-solutions/laravel-kit/src/Auth/routes.php');

/*
|--------------------------------------------------------------------------
| Application routes
|--------------------------------------------------------------------------
*/
