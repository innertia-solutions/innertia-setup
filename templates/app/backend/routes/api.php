<?php

use Illuminate\Support\Facades\Route;
use App\Apps\Backoffice\Auth\AuthController;
use App\Apps\Backoffice\Auth\PasswordController;
use App\Apps\Backoffice\Users\UsersController;
use App\Apps\Backoffice\Roles\RolesController;
use App\Apps\Backoffice\Permissions\PermissionsController;
use Innertia\Auth\Middleware\Authenticate;

/*
|--------------------------------------------------------------------------
| Innertia platform routes (innertia-solutions/laravel-kit)
|--------------------------------------------------------------------------
|
| Registers: auth (login/logout/refresh/me/otp/2fa/email-verify/social),
|            subscriptions, admin social-settings, file upload/download,
|            notifications, backoffice (users/roles/permissions).
|
*/
require base_path('vendor/innertia-solutions/laravel-kit/src/Auth/routes.php');
require base_path('vendor/innertia-solutions/laravel-kit/src/Backoffice/routes.php');

/*
|--------------------------------------------------------------------------
| Application routes
|--------------------------------------------------------------------------
*/
