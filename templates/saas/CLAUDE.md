# {{PROJECT_NAME}} — SaaS (Laravel + Nuxt + Multitenancy)

## Stack
- Laravel 13, PHP 8.3 (backend/) — REST API con multitenancy via `stancl/tenancy`
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL 16 (DB central + DBs por tenant), Redis 7
- Docker Compose
- `innertia-solutions/laravel-kit` — DataTable, ActivityLogger, EntityHistory, HasNanoId, Auditable
- `tymon/jwt-auth` — autenticación JWT
- `stancl/tenancy` — multitenancy automática por tenant DB

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate` — migraciones de la DB central
- `docker compose exec api php artisan tenants:migrate` — migraciones de los tenants
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan tenants:list`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture

- `backend/` — Laravel API con multitenancy. Cada tenant tiene su propia base de datos.
- `frontend/` — Nuxt 3. Consume la API vía proxy `/api/**` → `http://api:80/**`.

### Multitenancy

- `stancl/tenancy` con separación por base de datos (database per tenant).
- Rutas centrales (admin/platform) en `routes/api.php`.
- Rutas de tenant en `routes/tenant.php` (bajo middleware `InitializeTenancyByDomain` o `InitializeTenancyByRequestData`).
- Configurar identificación de tenant en `config/tenancy.php`.

### DDD personalizado (backend/)

```
backend/app/
├── Domains/          # Entidades de negocio compartidas.
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
├── Apps/             # Capa HTTP. Un subdirectorio por módulo/app.
│   ├── Admin/        # Rutas de administración central (landlord)
│   │   └── {Domain}/Controllers/
│   └── Tenant/       # Rutas de tenant
│       └── {Domain}/Controllers/
│
└── Platform/         # Infraestructura compartida.
    ├── Contracts/    # UseCase, Executable, GateInterface
    ├── Traits/       # Auditable, HasHistory, HasNanoId
    ├── Models/       # ActivityLog, EmailLog
    ├── Services/
    └── Facades/
```

### Reglas

- Controllers solo validan y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `App\Platform\Contracts\UseCase`.
- Models viven en `app/Domains/{D}/Models/`. Nunca en `app/Models/`.
- Los modelos de tenant deben usar el trait `BelongsToTenant` o estar en el contexto de tenant.
- Todos los modelos usan `HasNanoId` en vez de auto-increment IDs.

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
