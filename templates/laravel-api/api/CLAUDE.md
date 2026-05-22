# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 13, PHP 8.3
- PostgreSQL 16, Redis 7
- Docker + Xdebug
- `innertia-solutions/laravel-innertia` — modo `api`: clients, API keys, middleware apikey, Olimpo
- Autenticación: X-Api-Key (sin JWT, sin usuarios)

## Estructura del repositorio

```
{{PROJECT_NAME}}/
├── api/          ← código Laravel (este directorio)
└── compose.yml   ← Docker Compose en el root
```

## Commands
- `docker compose up` — inicia todos los servicios (correr desde el root del repo)
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan route:list`

## Ports
- API: http://localhost:{{APP_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture — DDD

```
app/
├── Domains/              # Lógica de negocio. Un subdirectorio por dominio.
│   └── {Domain}/
│       ├── Models/       # Eloquent models
│       ├── UseCases/     # Lógica de negocio (extienden UseCase)
│       ├── Services/     # Servicios auxiliares reutilizables
│       ├── Events/       # DomainEvents
│       ├── Listeners/    # Escuchan eventos de dominio
│       └── Observers/    # Eloquent observers
│
└── Api/                  # Capa HTTP. Organizada por grupo de endpoints.
    └── {Group}/
        └── {Resource}Controller.php
```

### Reglas

- Controllers solo validan input y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `\Innertia\Platform\Contracts\UseCase`, reciben parámetros en constructor, devuelven resultado desde `execute()`.
- Models viven en `app/Domains/{Domain}/Models/`. Nunca en `app/Models/`.
- El client autenticado se obtiene del request: `$request->attributes->get('client')`.
- IDs son UUID. Usar el trait `\Innertia\Platform\Traits\HasUuid`.

### Ejemplo de UseCase

```php
// app/Domains/Orders/UseCases/CreateOrder.php
namespace App\Domains\Orders\UseCases;

use App\Domains\Orders\Models\Order;
use Innertia\Api\Models\Client;
use Innertia\Platform\Contracts\UseCase;

class CreateOrder extends UseCase
{
    public function __construct(
        public readonly Client $client,
        public readonly string $description,
        public readonly float  $total,
    ) {}

    public function execute(): Order
    {
        return Order::create([
            'client_id'   => $this->client->id,
            'description' => $this->description,
            'total'       => $this->total,
        ]);
    }
}

// Uso sincrónico:
$order = (new CreateOrder(client: $client, description: '...', total: 99.9))->execute();

// Uso asincrónico (cola):
(new CreateOrder(...))->onQueue();
(new CreateOrder(...))->onQueue('critical');
(new CreateOrder(...))->delay(now()->addMinutes(5));
```

### Ejemplo de Controller

```php
// app/Api/Orders/OrdersController.php
namespace App\Api\Orders;

use App\Domains\Orders\UseCases\CreateOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdersController
{
    public function store(Request $request): JsonResponse
    {
        $data   = $request->validate([
            'description' => 'required|string',
            'total'       => 'required|numeric|min:0',
        ]);
        $client = $request->attributes->get('client');

        $order = (new CreateOrder(
            client:      $client,
            description: $data['description'],
            total:       $data['total'],
        ))->execute();

        return response()->json($order, 201);
    }
}
```
