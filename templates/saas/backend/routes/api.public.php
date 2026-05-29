<?php

// Rutas base/públicas (SaaS). Los registrars auto-aplican su middleware.
// Olimpo (admin de plataforma) lo registra el OlimpoServiceProvider.

\Innertia\Saas\Auth\Routes::register();   // /auth/* (login, otp, 2fa, email, password, oauth, me, refresh, logout)
\Innertia\Saas\Routes::register();        // /status

// Rutas públicas propias del producto van aquí.
