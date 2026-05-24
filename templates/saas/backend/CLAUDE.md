# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 13, PHP 8.3
- PostgreSQL 16, Redis 7
- Docker + Xdebug
- `innertia-solutions/laravel-innertia` — Auth (JWT), DataTable, ActivityLogger, EntityHistory, UseCase, DomainEvent, Webhooks, Settings
- `tymon/jwt-auth` — autenticación JWT

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan route:list`

## Ports
- API: http://localhost:{{APP_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture — DDD personalizado

```
app/
├── Domains/          # Entidades de negocio. Un subdirectorio por dominio.
│   └── {Domain}/
│       ├── Models/       # Eloquent models
│       ├── UseCases/     # Lógica de negocio (extienden \Innertia\Platform\Contracts\UseCase)
│       ├── Services/     # Lógica auxiliar reutilizable
│       ├── Events/       # DomainEvents (extienden \Innertia\Platform\Events\DomainEvent)
│       ├── Listeners/    # Escuchan eventos
│       ├── Gates/        # Autorizaciones (extienden \Innertia\Platform\Contracts\DomainGate)
│       ├── Enums/        # Enumeraciones PHP 8.1+
│       ├── Mails/        # Mailables (extienden \Innertia\Mail\InnertiaMailable)
│       └── Observers/    # Eloquent observers
│
└── Apps/             # Capa HTTP. Un subdirectorio por app/módulo.
    └── {AppName}/
        └── {Domain}/
            └── Controllers/   # Controladores REST (delegan a UseCases)
```

### Reglas

- Controllers solo validan input y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `\Innertia\Platform\Contracts\UseCase`, reciben parámetros en constructor y devuelven resultado desde `execute()`.
- Models viven en `app/Domains/{D}/Models/`. Nunca en `app/Models/`.
- Rutas API en `routes/api.php` agrupadas por módulo.
- Usar `Auditable` y `HasHistory` (de laravel-innertia) en modelos que requieren trazabilidad.
- IDs son UUID — los modelos que extiendan `\Innertia\Models\User` ya lo incluyen. Para otros modelos, usar el trait `\Innertia\Traits\HasUuid`.

### Ejemplo de UseCase

```php
// app/Domains/Orders/UseCases/CreateOrder.php
namespace App\Domains\Orders\UseCases;

use App\Domains\Orders\Models\Order;
use Innertia\Platform\Contracts\UseCase;

class CreateOrder extends UseCase
{
    public function __construct(
        public readonly string $customerId,
        public readonly float  $total,
    ) {}

    public function execute(): Order
    {
        return Order::create([
            'customer_id' => $this->customerId,
            'total'       => $this->total,
        ]);
    }
}

// Uso sincrónico:
$order = (new CreateOrder(customerId: '...', total: 99.9))->execute();

// Uso asincrónico (cola):
(new CreateOrder(customerId: '...', total: 99.9))->onQueue();
(new CreateOrder(customerId: '...', total: 99.9))->onQueue('critical');
(new CreateOrder(customerId: '...', total: 99.9))->delay(now()->addMinutes(5));
```

### Ejemplo de Controller

```php
// app/Apps/BackOffice/Orders/Controllers/OrdersController.php
namespace App\Apps\BackOffice\Orders\Controllers;

use App\Domains\Orders\UseCases\CreateOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|string',
            'total'       => 'required|numeric|min:0',
        ]);

        $order = (new CreateOrder(
            customerId: $data['customer_id'],
            total:      $data['total'],
        ))->execute();

        return response()->json($order, 201);
    }
}
```

## Organizations (opt-in)

Second-level scoping that stacks ON TOP of the existing Tenant layer. Useful when a single tenant manages multiple isolated business units.

**Model:**
- `Innertia::tenant()` — outer scope (existing)
- `Innertia::organization()` — inner scope (this feature)
- Both must resolve for a request to proceed under `organization.require` middleware

**When activated:**
- Trait `HasOrganization` on scoped models (use alongside `HasTenant`)
- Middleware: `tenant.resolve → tenant.require → organization.resolve → organization.require`
- Client sends both: `X-Tenant: acme` + `X-Organization: north-america`
- Optional consolidated view: add `X-Consolidated: true` — the middleware populates `scope()` from `auth()->user()->accessibleOrganizationIds()` (implement that method on your User to return the org IDs the user can read)
- Roles can be global (per tenant) or scoped (per organization). `$user->hasRole('admin', organizationId: 5)`.
- Permission cache keys auto-include the active org id

**Activation:**

```bash
# 1. Uncomment + configure 'organizations' block in config/innertia.php
docker compose exec api php artisan innertia:organization:install
docker compose exec api php artisan migrate
docker compose exec api php artisan innertia:organization:check   # CI guard
```

Full reference: `vendor/innertia-solutions/laravel-innertia/docs/organizations.md`.
