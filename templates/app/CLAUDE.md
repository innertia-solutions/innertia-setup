# {{PROJECT_NAME}} — App (Laravel + Nuxt)

## Stack
- Laravel 11 (backend/) — REST API
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL 16, Redis 7
- Docker Compose

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture
- backend/ es un Laravel estándar. Rutas API en routes/api.php.
- frontend/ es Nuxt 3 consumiendo la API via NUXT_PUBLIC_API_URL.
- Se comunican dentro de Docker via nombres de servicio (api, database, redis).
