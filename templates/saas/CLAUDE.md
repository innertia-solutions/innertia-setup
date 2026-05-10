# {{PROJECT_NAME}} — SaaS (Laravel + Nuxt + Multitenancy)

## Stack
- Laravel 11 (backend/) — REST API con multitenancy via `stancl/tenancy`
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL 16 (DB central + DBs por tenant), Redis 7
- Docker Compose

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate` — migraciones de la DB central
- `docker compose exec api php artisan tenants:migrate` — migraciones de los tenants
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture
- Multitenancy via `stancl/tenancy`. Configurar en config/tenancy.php.
- Rutas centrales en routes/api.php. Rutas por tenant en routes/tenant.php.
- Los tenants se identifican por subdominio o header (según configuración).
- frontend/ consume la API via NUXT_PUBLIC_API_URL.
