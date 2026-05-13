# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 13, PHP 8.3
- PostgreSQL 16, Redis 7
- Docker + Xdebug
- `innertia-solutions/laravel-kit` — Auth (JWT), RBAC (roles y permisos), DataTable, ActivityLogger, EntityHistory, UseCase, DomainEvent, Webhooks, Settings
- `tymon/jwt-auth` — autenticación JWT

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan migrate --seed`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan route:list`
- `docker compose exec api php artisan vendor:publish --tag=innertia-migrations` — copia migraciones del kit a database/migrations/ para inspeccionarlas o personalizarlas

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
- Usar `Auditable` y `HasHistory` (de laravel-kit) en modelos que requieren trazabilidad.
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
