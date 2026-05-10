# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 11
- PostgreSQL 16
- Redis 7
- Docker + Xdebug

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate` — corre migraciones
- `docker compose exec api php artisan tinker` — REPL
- `docker compose exec api php artisan test` — tests

## Ports
- API: http://localhost:{{APP_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}
