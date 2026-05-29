# {{PROJECT_NAME}} — App (Laravel + Nuxt)

## Stack
- Laravel 13, PHP 8.3 (backend/) — REST API
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL 16, Redis 7
- Docker Compose
- `innertia-solutions/laravel-innertia` — Auth (JWT), RBAC (roles y permisos), DataTable, ActivityLogger, EntityHistory, HasNanoId, Auditable
- JWT propio (firebase/php-jwt vía el codec de innertia-laravel)

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan route:list`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture

- `backend/` — Laravel API. Los servicios Docker se comunican por nombre: `api`, `database`, `redis`.
- `frontend/` — Nuxt 3. Consume la API vía proxy `/api/**` → `http://api:80/**`.

### DDD personalizado (backend/)

```
backend/app/
├── Domains/          # Entidades de negocio. Un subdirectorio por dominio.
│   └── {Domain}/
│       ├── Models/       # Eloquent models
│       ├── UseCases/     # Lógica de negocio (extienden UseCase)
│       ├── Services/     # Lógica auxiliar reutilizable
│       ├── Events/       # Eventos de dominio
│       ├── Listeners/    # Escuchan eventos
│       ├── Gates/        # Autorizaciones del dominio
│       ├── Enums/        # Enumeraciones PHP 8.1+
│       ├── Mails/        # Mailables del dominio
│       └── Observers/    # Eloquent observers
│
├── Apps/             # Capa HTTP. Un subdirectorio por módulo.
│   └── {AppName}/
│       └── {Domain}/
│           └── Controllers/
│
└── Platform/         # Infraestructura compartida.
    ├── Contracts/    # UseCase, Executable, GateInterface
    ├── Traits/       # Auditable, HasHistory, HasNanoId
    ├── Models/       # ActivityLog, EmailLog
    ├── Services/     # Servicios de infraestructura
    └── Facades/      # DataTable, ActivityLogger
```

### Reglas

- Controllers solo validan y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `App\Platform\Contracts\UseCase` y tienen un único método `execute()`.
- Models viven en `app/Domains/{D}/Models/`. Nunca en `app/Models/`.
- Todos los modelos usan `HasNanoId` en vez de auto-increment IDs.
- Usar `Auditable` y `HasHistory` en modelos que requieren trazabilidad.
- Rutas API en `routes/api.php` agrupadas por módulo.

### Ejemplo de UseCase

```php
namespace App\Domains\Users\UseCases;

use App\Domains\Users\Models\User;
use App\Platform\Contracts\UseCase;

class CreateUser extends UseCase
{
    public function execute(string $name, string $email, string $password): User
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
        ]);
    }
}

// Uso: (new CreateUser)($name, $email, $password);
```

## Organizations (opt-in)

Optional second-level scoping. The `app` template runs single-tenant, but you can still enable Organizations for **logical separation within the app** — e.g. departments, business units, regions — without introducing a full SaaS multi-tenant model.

Activation lives in `backend/config/innertia.php` (see the commented `organizations` block). Full guide: `vendor/innertia-solutions/laravel-innertia/docs/organizations.md`.
