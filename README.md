# innertia-setup

CLI interactivo para crear proyectos del stack Innertia (Laravel + Nuxt + Docker) desde boilerplates.

## Uso

```bash
# Desde cualquier máquina con Node instalado
# (crea el proyecto en el directorio actual)
npx github:guillermofarias/innertia-setup

# Alias recomendado en ~/.zshrc para correr desde ~/Sites/inertia/
alias new-project="cd ~/Sites/inertia && npx github:guillermofarias/innertia-setup"
```

## Desarrollo local

```bash
# Interactivo
npm run dev

# Directo sin prompts
npx tsx scripts/create.ts <nombre-proyecto> <template>
```

## Templates

| Template | Descripción |
|---|---|
| `nuxt-landing` | Nuxt 3 standalone para landing pages |
| `laravel-api` | Laravel API standalone con Docker |
| `app` | Monorepo Laravel + Nuxt + Docker Compose |
| `saas` | Monorepo Laravel + Nuxt + Multitenancy + TanStack Query |

### Organizations (opt-in)

Both the `app` and `saas` templates ship with the Organizations feature scaffolded but **disabled**. It's a second-level scoping layer (separate business units, departments, or client orgs within a tenant) that you can enable by uncommenting the `organizations` block in `backend/config/innertia.php` after the project is created.

- `app` template → use for logical separation within a single-tenant app
- `saas` template → use to stack a second scope inside each tenant
- `laravel-api` template → not applicable (api mode blocks the feature)

Full guide once installed: `vendor/innertia-solutions/laravel-innertia/docs/organizations.md`.

---

## Template `saas` — Stack completo

### Servicios
| Servicio | Puerto |
|---|---|
| Frontend (Nuxt) | 3000 |
| API (Laravel) | 8100 |
| PostgreSQL | 5437 |
| Redis | 63791 |

### Arquitectura frontend

El template `saas` incluye un patrón estandarizado para todas las entidades del dominio:

```
Componente/Página     renderiza, maneja eventos UI
      ↕
use{Entity}.js        qué datos, cuándo, cómo se invalidan (TanStack Query)
      ↕
useApi                cómo viaja la petición HTTP (headers, auth, X-Tenant)
      ↕
QueryClient           cache compartido entre componentes
      ↕
Laravel API
```

Agregar una entidad nueva (`Invoices` como ejemplo):

```
1. backend/app/Domains/Invoices/         ← modelos + usecases
2. frontend/app/composables/useInvoices.js  ← queries + mutations
3. frontend/app/pages/backoffice/invoices/  ← páginas que consumen el composable
```

Ver `templates/saas/CLAUDE.md` o `templates/saas/README.md` para el patrón completo con código.

### Layers npm disponibles

| Paquete | Versión | Descripción |
|---|---|---|
| `@innertia-solutions/nuxt-app` | `^0.1.8` | Auth, useApi, useEntity pattern, TanStack Query |
| `@innertia-solutions/nuxt-saas` | `^0.1.12` | Multitenancy, subdomain, tenant-error |
| `@innertia-solutions/nuxt-theme-spark` | `^0.1.19` | UI: DataTable, Table, Table/Standard, Table/Kanban, Table/Grid, Table/List, Table/Database, TableFilter, TableExportable, Forms, Toast, Admin layouts |

---

## Agregar un template

1. Crear `templates/<nombre>/` con los archivos del boilerplate
2. Agregar la entrada en `cli/src/templates.ts`
3. Usar `{{PLACEHOLDER}}` para valores sustituibles

| Placeholder | Valor |
|---|---|
| `{{PROJECT_NAME}}` | nombre del proyecto |
| `{{PROJECT_NAME_UPPER}}` | nombre en mayúsculas |
| `{{DB_PASSWORD}}` | igual al nombre del proyecto |
| `{{APP_PORT}}` | `8100` (fijo) |
| `{{DB_PORT}}` | `5437` (fijo) |
| `{{REDIS_PORT}}` | `63791` (fijo) |
| `{{FRONTEND_PORT}}` | `3000` (fijo) |
| `{{XDEBUG_PORT}}` | `9003` (fijo) |
