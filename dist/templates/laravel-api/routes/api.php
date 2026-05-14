<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes (innertia-solutions/laravel-innertia)
|--------------------------------------------------------------------------
|
| POST /auth/login
| POST /auth/otp/send  |  POST /auth/otp/verify
| POST /auth/2fa/verify
| POST /auth/email/verify/send  |  GET /auth/email/verify
| POST /auth/password/change    |  POST /auth/password/set
| GET  /auth/me  |  POST /auth/refresh  |  POST /auth/logout  (protected)
| POST /auth/2fa/enable  |  POST /auth/2fa/disable            (protected)
|
*/
require base_path('vendor/innertia-solutions/laravel-innertia/src/Auth/routes.php');

/*
|--------------------------------------------------------------------------
| Application routes
|--------------------------------------------------------------------------
*/
