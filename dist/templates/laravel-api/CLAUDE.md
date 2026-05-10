# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 13, PHP 8.3
- PostgreSQL 16, Redis 7
- Docker + Xdebug
- `innertia-solutions/laravel-kit` — DataTable, ActivityLogger, EntityHistory, HasNanoId, Auditable
- `spatie/laravel-permission` — roles y permisos
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

El código de negocio vive en `app/` con esta estructura:

```
app/
├── Domains/          # Entidades de negocio. Un subdirectorio por dominio.
│   └── {Domain}/
│       ├── Models/       # Eloquent models
│       ├── UseCases/     # Lógica de negocio orquestada (extienden UseCase)
│       ├── Services/     # Lógica auxiliar reutilizable
│       ├── Events/       # Eventos de dominio
│       ├── Listeners/    # Escuchan eventos
│       ├── Gates/        # Autorizaciones del dominio
│       ├── Enums/        # Enumeraciones PHP 8.1+
│       ├── Mails/        # Mailables del dominio
│       └── Observers/    # Eloquent observers
│
├── Apps/             # Capa de aplicación (HTTP). Un subdirectorio por app/módulo.
│   └── {AppName}/
│       └── {Domain}/
│           └── Controllers/   # Controladores REST (delegan a UseCases)
│
└── Platform/         # Infraestructura compartida. No contiene lógica de negocio.
    ├── Contracts/    # Interfaces base: UseCase, Executable, GateInterface
    ├── Traits/       # Traits transversales: Auditable, HasHistory, HasNanoId
    ├── Models/       # Modelos de infraestructura (ActivityLog, EmailLog)
    ├── Services/     # Servicios de infraestructura
    └── Facades/      # Facades propias (DataTable, ActivityLogger)
```

### Reglas

- Los **Controllers** (`app/Apps/`) solo validan input y delegan a UseCases. Sin lógica de negocio.
- Los **UseCases** (`app/Domains/{D}/UseCases/`) extienden `App\Platform\Contracts\UseCase` y tienen un único método `execute()`.
- Los **Models** viven en `app/Domains/{D}/Models/`. Nunca en `app/Models/`.
- Las rutas API se definen en `routes/api.php` agrupadas por módulo.
- Todos los modelos usan `HasNanoId` (de laravel-kit) en vez de auto-increment IDs.
- Usar `Auditable` y `HasHistory` (de laravel-kit) en modelos que requieren trazabilidad.

### Ejemplo de UseCase

```php
// app/Domains/Users/UseCases/CreateUser.php
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

// Uso desde un controller:
(new CreateUser)($name, $email, $password);
// o:
(new CreateUser)->execute($name, $email, $password);
```

### Ejemplo de Controller

```php
// app/Apps/BackOffice/Users/Controllers/UsersController.php
namespace App\Apps\BackOffice\Users\Controllers;

use App\Domains\Users\UseCases\CreateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $user = (new CreateUser)($data['name'], $data['email'], $data['password']);

        return response()->json($user, 201);
    }
}
```
