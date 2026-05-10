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
| `saas` | Monorepo Laravel + Nuxt + Multitenancy |

## Desarrollo

```bash
npm install
npm run dev      # corre el CLI sin build
npm test         # corre los tests
npm run build    # compila a dist/
```

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
