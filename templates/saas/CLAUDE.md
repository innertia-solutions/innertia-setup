# {{PROJECT_NAME}} — SaaS (Laravel + Nuxt + Multitenancy)

## Stack
- Laravel 13, PHP 8.4 (`backend/`) — REST API con multitenancy via `Innertia::tenant()`
- Nuxt 3 (`frontend/`) — SPA/SSR, layers: `nuxt-saas` + `nuxt-theme-spark`
- PostgreSQL 16, Redis 7
- Docker Compose
- `innertia-solutions/laravel-innertia` — DataTable, ActivityLogger, EntityHistory, HasNanoId, Auditable, TenantManager
- `tymon/jwt-auth` — autenticación JWT
- `@tanstack/vue-query` — cache y orquestación de fetching (via `nuxt-app` layer)

## Commands
- `docker compose up` — inicia todos los servicios
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`
- `docker compose exec api php artisan test`
- `docker compose exec api php artisan tenant:list`
- `docker compose exec api php artisan tenant:create acme "Acme Corp"`
- `docker compose exec api php artisan tenant:show acme`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture

- `backend/` — Laravel API con multitenancy single-DB. Todos los modelos tenant usan `tenant_id`.
- `frontend/` — Nuxt 3. Consume la API vía proxy `/api/**` → `http://api:80/**`.

### Multitenancy

Identificación de tenant vía header `X-Tenant: {key}`. El middleware `tenant.resolve` lo activa automáticamente en cada request.

```php
Innertia::tenant()           // Tenant|null
Innertia::activate('acme')  // activa manualmente (CLI/tinker)
Innertia::deactivate()
```

- Rutas públicas (sin auth): `routes/api.public.php`
- Rutas privadas (con auth): `routes/api.private.php`

Los modelos con `HasTenant` aplican un global scope automático por `tenant_id`.
Los UseCases capturan el `tenant_key` en construcción y lo restauran al ejecutarse en queue.

---

## Frontend — Arquitectura de Entidades

### Capas

```
Componente/Página          →  renderiza, maneja eventos UI
use{Entity}.js             →  qué datos, cuándo, cómo se invalidan (TanStack Query)
useApi                     →  cómo viaja la petición HTTP (headers, auth, X-Tenant)
QueryClient                →  cache compartido entre componentes (TanStack Query)
Laravel API                →  devuelve los datos
```

Cada capa solo habla con la de al lado. El componente no conoce URLs ni headers.

### Patrón use{Entity}

Cada entidad de negocio tiene su propio composable en `frontend/app/composables/`:

```js
// frontend/app/composables/useInvoices.js
export function useInvoices() {
  const api = useApi()
  const queryClient = useQueryClient()

  // Queries — reactivas, cacheadas, auto-refetch cuando cambia queryKey
  const list = (params = {}) => useQuery({
    queryKey: computed(() => ['invoices', toValue(params)]),
    queryFn: () => api.post('backoffice/invoices', toValue(params)),
  })

  const detail = (id) => useQuery({
    queryKey: computed(() => ['invoices', toValue(id)]),
    queryFn: () => api.get(`backoffice/invoices/${toValue(id)}`),
    enabled: computed(() => !!toValue(id)),
  })

  // Mutations — invalidan el cache automáticamente al tener éxito
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

### Uso en componentes

```vue
<script setup>
// SIEMPRE en setup(), nunca dentro de callbacks o event handlers
const { list, create } = useInvoices()

// Query reactiva — si filters cambia, re-fetcha automáticamente
const filters = ref({ status: 'active' })
const { data: invoices, isLoading } = list(filters)

// Mutación lista para ejecutar
const { mutate: createInvoice, isPending } = create()
</script>

<template>
  <Table :data="invoices" :loading="isLoading" ... />
  <button @click="createInvoice({ ... })" :disabled="isPending">Crear</button>
</template>
```

### Invalidación cruzada

Cuando un usuario guarda, la tabla se actualiza sola en cualquier componente activo:

```js
// En useInvoices.js — onSuccess invalida el cache
onSuccess: () => queryClient.invalidateQueries({ queryKey: ['invoices'] })
// Todos los componentes con list() activo re-fetchan automáticamente
```

### Estructura de archivos por entidad

```
Backend                              Frontend
────────────────────────────────────────────────────────
Domains/Invoices/                    composables/useInvoices.js
  Models/Invoice.php
  UseCases/CreateInvoice.php         pages/backoffice/invoices/
  UseCases/UpdateInvoice.php           index.vue    → useInvoices().list
                                       [id].vue     → useInvoices().detail + .update
                                       create.vue   → useInvoices().create
```

Backend y frontend espejados por entidad. `useEntity.js` en `nuxt-app` es la plantilla base.

### Componentes de tabla disponibles

- `<DataTable>` — tabla server-side simple (paginación, sort, search, cache, export)
- `<Table>` — tabla con TanStack Table (+ visibility, reordenamiento de columnas, filtros por columna)
- `<TableDownloadDropdown>` — botón de exportación (xlsx, csv, pdf, json)

```vue
<Table
  name="invoices"
  endpoint="backoffice/invoices"
  :columns="[
    { key: 'number', label: 'Número', sortable: true },
    { key: 'amount', label: 'Monto', sortable: true },
    { key: 'status', label: 'Estado', filterable: true },
  ]"
  :search="search"
  :cached="true"
  ref="tableRef"
  @row-click="(row) => navigateTo(`/backoffice/invoices/${row.id}`)"
/>
```

---

## Backend — DDD personalizado

```
backend/app/
├── Domains/          # Entidades de negocio
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
└── Apps/             # Capa HTTP
    └── {AppName}/
        └── {Domain}/Controllers/
```

### Reglas backend

- Controllers solo validan input y delegan a UseCases. Sin lógica de negocio.
- UseCases extienden `\Innertia\Platform\Contracts\UseCase`, reciben parámetros en constructor.
- Models tenant usan `HasTenant` trait — global scope automático por `tenant_id`.
- IDs son UUID via `HasUuid` trait.
