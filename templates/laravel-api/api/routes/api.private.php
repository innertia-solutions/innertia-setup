<?php

use Illuminate\Support\Facades\Route;

// ── Rutas privadas (requieren X-Api-Key) ──────────────────────────────────────
// Route::middleware('verify.api.key')->group(function () {
//     // tus rutas aquí
//     // La organización autenticada se obtiene del request:
//     //   $request->attributes->get('organization')  → Organization model
//     //   $request->attributes->get('api_key')        → ApiKey model
// });
