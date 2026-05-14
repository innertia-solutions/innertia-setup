<?php

// Punto de entrada de rutas API (SaaS mode).
// Rutas públicas (sin auth): api.public.php
// Rutas privadas (con auth): api.private.php

require __DIR__ . '/api.public.php';
require __DIR__ . '/api.private.php';
