# {{PROJECT_NAME}}

SaaS multi-tenant — Laravel 13 + Nuxt 3 + PostgreSQL + Docker.

## Inicio rápido

```bash
docker compose up
docker compose exec api php artisan migrate
docker compose exec api php artisan tenant:create acme "Acme Corp"
```

Accede en `http://acme.localhost:{{FRONTEND_PORT}}`.

## Servicios

| Servicio  | URL                              |
|-----------|----------------------------------|
| Frontend  | http://localhost:{{FRONTEND_PORT}} |
| API       | http://localhost:{{APP_PORT}}    |
| DB        | localhost:{{DB_PORT}}            |
| Redis     | localhost:{{REDIS_PORT}}         |

## Estructura

```
{{PROJECT_NAME}}/
├── backend/          # Laravel 13 — API REST + DDD
├── frontend/         # Nuxt 3 — SPA/SSR
└── docker-compose.yml
```

---

## Backend (`backend/`)

### Stack
- Laravel 13, PHP 8.4
- `innertia-solutions/laravel-kit` — DataTable, ActivityLogger, EntityHistory, HasNanoId, TenantManager
- `tymon/jwt-auth` — autenticación JWT
- Multitenancy single-DB via `X-Tenant` header

### Estructura DDD

```
app/
├── Domains/{Domain}/
│   ├── Models/
│   ├── UseCases/        # lógica de negocio, extienden UseCase
│   ├── Services/
│   ├── Events/
│   ├── Listeners/
│   ├── Gates/
│   └── Enums/
└── Apps/{AppName}/{Domain}/Controllers/
```

### Reglas
- **Controllers** — solo validan input y delegan a UseCases
- **UseCases** — toda la lógica de negocio, reciben parámetros en constructor
- **Models tenant** — usan `HasTenant` (global scope por `tenant_id` automático)
- **IDs** — UUID via `HasUuid`

### Comandos útiles

```bash
# Migraciones
docker compose exec api php artisan migrate

# Tenants
docker compose exec api php artisan tenant:list
docker compose exec api php artisan tenant:create {slug} "{Nombre}"
docker compose exec api php artisan tenant:show {slug}

# Testing
docker compose exec api php artisan test

# Tinker
docker compose exec api php artisan tinker
>>> Innertia::activate('acme')
>>> App\Domains\Users\Models\User::first()
```

### Rutas API

```
routes/
├── api.php          # incluye public + private
├── api.public.php   # sin auth (login, register, etc.)
└── api.private.php  # con auth (JWT middleware)
```

---

## Frontend (`frontend/`)

### Stack
- Nuxt 3 con SSR
- `@innertia-solutions/nuxt-saas` — multitenancy, detección de subdomain, tenant-error
- `@innertia-solutions/nuxt-theme-spark` — componentes UI (Tailwind)
- `@tanstack/vue-query` — cache y orquestación de fetching (via `nuxt-app`)

### Arquitectura de capas

```
Componente/Página     renderiza, maneja eventos UI
      ↕
use{Entity}.js        qué datos, cuándo, cómo se invalidan
      ↕
useApi                cómo viaja la petición HTTP (auth, X-Tenant, base URL)
      ↕
QueryClient           cache compartido entre componentes
      ↕
Laravel API
```

### Crear una nueva entidad

**1. Composable** — `frontend/app/composables/useInvoices.js`

```js
export function useInvoices() {
  const api = useApi()
  const queryClient = useQueryClient()

  const list = (params = {}) => useQuery({
    queryKey: computed(() => ['invoices', toValue(params)]),
    queryFn: () => api.post('backoffice/invoices', toValue(params)),
  })

  const detail = (id) => useQuery({
    queryKey: computed(() => ['invoices', toValue(id)]),
    queryFn: () => api.get(`backoffice/invoices/${toValue(id)}`),
    enabled: computed(() => !!toValue(id)),
  })

  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['invoices'] })

  const create = () => useMutation({
    mutationFn: (data) => api.post('backoffice/invoices', data),
    onSuccess: invalidate,
  })

  const update = () => useMutation({
    mutationFn: ({ id, ...data }) => api.put(`backoffice/invoices/${id}`, data),
    onSuccess: (_, { id }) => {
      queryClient.invalidateQueries({ queryKey: ['invoices', id] })
      invalidate()
    },
  })

  const remove = () => useMutation({
    mutationFn: (id) => api.delete(`backoffice/invoices/${id}`),
    onSuccess: invalidate,
  })

  return { list, detail, create, update, remove }
}
```

**2. Páginas** — `frontend/app/pages/backoffice/invoices/`

```vue
<!-- index.vue -->
<script setup>
definePageMeta({ middleware: 'auth' })
const search = ref('')
const { list } = useInvoices()
const { data, isLoading } = list({ search })
</script>

<template>
  <AdminPage title="Facturas">
    <FullTable name="invoices" endpoint="backoffice/invoices"
      :columns="[{ key: 'number', label: 'N°', sortable: true }]"
      :search="search" :cached="true"
      @row-click="(row) => navigateTo(`/backoffice/invoices/${row.id}`)" />
  </AdminPage>
</template>
```

```vue
<!-- [id].vue -->
<script setup>
definePageMeta({ middleware: 'auth' })
const route = useRoute()
const { detail, update } = useInvoices()
const { data: invoice, isLoading } = detail(route.params.id)
const { mutate: save, isPending } = update()
</script>
```

```vue
<!-- create.vue -->
<script setup>
definePageMeta({ middleware: 'auth' })
const { create } = useInvoices()
const { mutate, isPending, error } = create()
</script>
```

### Rutas de auth

```
/backoffice/login                    login principal
/backoffice/auth/forgot-password     solicitar reset
/backoffice/auth/reset-password      nuevo password con token
/backoffice/auth/set-password        activación de cuenta
/backoffice/auth/change-password     cambio voluntario (requiere auth)
/backoffice/auth/2fa                 verificación 2FA
/backoffice/auth/otp                 verificación OTP
```

### Componentes disponibles

```vue
<!-- Tabla simple server-side -->
<DataTable name="..." endpoint="..." :columns="[...]" :search="search" />

<!-- Tabla avanzada con TanStack (visibility, reordenamiento, filtros por columna) -->
<FullTable name="..." endpoint="..." :columns="[...]" :search="search" :cached="true"
  ref="tableRef" @row-click="..." />

<!-- Exportar tabla -->
<TableDownloadDropdown :table-ref="tableRef" />

<!-- Layout de backoffice -->
<AdminPage title="Título">...</AdminPage>

<!-- Formularios -->
<FormsInput v-model="value" label="Label" />
<FormsSelect v-model="value" :options="[]" />
<FormsSelectServer v-model="value" endpoint="..." />
```

### Variables de entorno

```env
NUXT_PUBLIC_API_URL=http://localhost:{{APP_PORT}}
NUXT_API_BASE_URL=http://api:80
NUXT_PUBLIC_APP_NAME={{PROJECT_NAME}}
NUXT_PUBLIC_APP_ENV=local
NUXT_PUBLIC_TIMEZONE=America/Santiago
```
