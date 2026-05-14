# {{PROJECT_NAME}} — SaaS (Laravel + Nuxt + Multitenancy)

## Stack
- Laravel 13, PHP 8.4 (backend/) — REST API con multitenancy via `Innertia::tenant()`
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL 16, Redis 7
- Docker Compose
- `innertia-solutions/laravel-innertia` — DataTable, ActivityLogger, EntityHistory, HasNanoId, Auditable, TenantManager
- `tymon/jwt-auth` — autenticación JWT

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan tenant:list`
- `docker compose exec api php artisan tenant:create acme "Acme Corp"`
- `docker compose exec api php artisan tenant:show acme`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture

- `backend/` — Laravel API con multitenancy single-DB. Todos los modelos tenant usan `tenant_id`.
- `frontend/` — Nuxt 3. Consume la API vía proxy `/api/**` → `http://api:80/**`.

### Multitenancy

Identificación de tenant vía header `X-Tenant: {key}`. El middleware `tenant.resolve` lo activa automáticamente en cada request.

```php
// Obtener tenant activo en runtime:
Innertia::tenant()           // Tenant|null

// Activar un tenant manualmente (CLI/tinker):
Innertia::activate('acme')  // Tenant (busca en DB + setea contexto)

// Desactivar:
Innertia::deactivate()
```

- Rutas públicas (sin auth): `routes/api.public.php`
- Rutas privadas (con auth): `routes/api.private.php`
- Ambas se incluyen desde `routes/api.php`

Los modelos con `HasTenant` aplican un global scope automático por `tenant_id`.
Los UseCases capturan el `tenant_key` en construcción y lo restauran al ejecutarse en queue.

### DDD personalizado (backend/)

```
backend/app/
├── Domains/          # Entidades de negocio.
│   └── {Domain}/
│       ├── Models/
│       ├── UseCases/
│       ├── Services/
│       ├── Events/
│       ├── Listeners/
│       ├── Gates/
│       ├── Enums/
│       ├── Mails/
│       └── Observers/
│
└── Apps/             # Capa HTTP.
    └── {AppName}/
        └── {Domain}/Controllers/
```

### Reglas

- Controllers solo validan input y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `\Innertia\Platform\Contracts\UseCase`, reciben parámetros en constructor.
- Models tenant usan `HasTenant` trait — global scope automático por `tenant_id`.
- IDs son UUID via `HasUuid` trait.
