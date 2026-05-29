<?php

// Rutas privadas (SaaS). Cada registrar auto-aplica el stack privado:
// resuelve tenant → autentica → exige tenant activo → resuelve organización.

\Innertia\Saas\Backoffice::routes();      // users / roles / permissions / sessions
\Innertia\Saas\Notifications::routes();

// Descomentar según el producto:
// \Innertia\Saas\Organizations::routes();
// \Innertia\Saas\Teams::routes();
// \Innertia\Saas\Files::routes();
// \Innertia\Saas\Directories::routes();

// ── Dominio del producto ───────────────────────────────────────────────────
// Stack privado disponible en \Innertia\Saas\Routes::privateMiddleware().
