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

| Componente | Descripción |
|---|---|
| `<DataTable>` | Tabla server-side simple (paginación, sort, search, cache, export) |
| `<Table>` | TanStack Table (visibility, reordenamiento de columnas, filtros por columna) |
| `<Table.Standard>` | Tabla admin completa: search + filters panel + export. Default para backoffice. |
| `<Table.Grid>` | Vista grid/card — wraps DataTable con `viewMode="grid"` |
| `<Table.List>` | Scroll infinito con `useInfiniteQuery`, intersection observer |
| `<Table.Kanban>` | Estados como columnas, DnD entre estados, updates optimistas |
| `<Table.Database>` | Vista densa con edición de celdas inline (click → input → blur) |
| `<TableFilter>` | Panel de filtros reutilizable (`filterType: 'text'|'select'|'daterange'`) |
| `<TableExportable>` | Modal de exportación: formato, nombre de archivo, columnas a incluir |
| `<TableDownloadDropdown>` | Dropdown de exportación simple (xlsx, csv, pdf, json) |

```vue
<!-- Tabla admin estándar (recomendado) -->
<Table.Standard
  name="invoices"
  endpoint="backoffice/invoices"
  :columns="[
    { key: 'number', label: 'Número', sortable: true },
    { key: 'amount', label: 'Monto', sortable: true },
    { key: 'status', label: 'Estado', filterable: true, filterType: 'select', filterOptions: [{value:'paid',label:'Pagado'}] },
    { key: 'date', label: 'Fecha', filterType: 'daterange' },
  ]"
  :cached="true"
  @row-click="(row) => navigateTo(`/backoffice/invoices/${row.id}`)"
/>

<!-- Kanban -->
<Table.Kanban
  name="tasks"
  endpoint="backoffice/tasks"
  state-key="status"
  :states="[
    { key: 'todo', label: 'Pendiente', color: 'slate' },
    { key: 'in_progress', label: 'En progreso', color: 'blue' },
    { key: 'done', label: 'Listo', color: 'green' },
  ]"
  :move-mutation="(id, state) => api.patch(`backoffice/tasks/${id}`, { status: state })"
  @card-click="(row) => navigateTo(`/backoffice/tasks/${row.id}`)"
/>

<!-- Database (edición inline) -->
<Table.Database
  name="products"
  endpoint="backoffice/products"
  :columns="[
    { key: 'name', label: 'Nombre', editable: true },
    { key: 'price', label: 'Precio', editable: true, type: 'number' },
    { key: 'category', label: 'Categoría', editable: true, type: 'select', options: [{value:'a',label:'A'}] },
  ]"
  :update-mutation="(id, field, value) => api.patch(`backoffice/products/${id}`, { [field]: value })"
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

## Organizations (opt-in)

Optional second-level scoping that lives **inside** each Tenant. Useful when a single tenant has multiple business units that should not share data — e.g. a consulting firm tenant with many client orgs inside, or a holding tenant with multiple subsidiaries.

Stacks with Tenant: `Innertia::tenant()` resolves the outer scope, `Innertia::organization()` the inner one. Same user can have different roles per organization within the same tenant (e.g. admin in org A, viewer in org B).

Activation lives in `backend/config/innertia.php` (see the commented `organizations` block). Full guide: `vendor/innertia-solutions/laravel-innertia/docs/organizations.md`.
