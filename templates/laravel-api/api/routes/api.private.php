<?php

use Illuminate\Support\Facades\Route;

// ── Rutas privadas (requieren X-Api-Key) ──────────────────────────────────────
// Route::middleware('apikey')->group(function () {
//     // tus rutas aquí
// });

// ── Admin (Olimpo) ────────────────────────────────────────────────────────────
// Route::middleware('apikey:admin.access')->prefix('admin')->group(function () {
//     // gestión de api-keys, métricas, etc.
// });
