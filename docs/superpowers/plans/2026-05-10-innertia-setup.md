# innertia-setup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an interactive Node.js CLI that scaffolds Innertia stack projects (Laravel + Nuxt + Docker) from four boilerplate templates, substituting variables and installing AI rules automatically.

**Architecture:** The CLI (`cli/`) is a thin interactive layer — it collects user input via prompts and calls standalone Node scripts (`scripts/`). Scripts do all real work and can be run directly without the CLI (e.g. `npx tsx scripts/create.ts my-project app`). Two prompts only: project name and template type.

**Tech Stack:** Node.js 20+, TypeScript, clack (prompts), fs-extra (file ops), fast-glob (file scanning), tsup (build), tsx (dev runner), vitest (tests)

---

## File Map

```
innertia-setup/
├── cli/
│   ├── src/
│   │   ├── index.ts          # entry point — prompts only, calls scripts/create.ts
│   │   ├── prompts.ts        # clack interactive flow
│   │   └── templates.ts      # template registry (id, label, description)
│   ├── package.json
│   └── tsconfig.json
│
├── scripts/
│   ├── create.ts             # main script — orchestrates scaffold + post-install
│   ├── scaffold.ts           # copy template, substitute vars, generate .env
│   ├── post-install.ts       # git init + initial commit
│   └── __tests__/
│       └── scaffold.test.ts  # unit tests for substituteVariables, buildVars, generateEnvContent
│
└── templates/
    ├── nuxt-landing/
    │   ├── .claude/settings.json
    │   ├── CLAUDE.md
    │   ├── docker/dev/Dockerfile
    │   ├── .env.example
    │   ├── .gitignore
    │   └── nuxt.config.ts
    ├── laravel-api/
    │   ├── .claude/settings.json
    │   ├── CLAUDE.md
    │   ├── docker/dev/Dockerfile
    │   ├── docker/dev/php.ini
    │   ├── .env.example
    │   └── .gitignore
    ├── app/
    │   ├── backend/
    │   │   ├── docker/dev/Dockerfile
    │   │   ├── docker/dev/php.ini
    │   │   └── .env.example
    │   ├── frontend/
    │   │   ├── docker/dev/Dockerfile
    │   │   └── .env.example
    │   ├── .claude/settings.json
    │   ├── CLAUDE.md
    │   ├── compose.yml
    │   ├── compose.prod.yml
    │   ├── .env.example
    │   └── .gitignore
    └── saas/
        ├── backend/
        │   ├── docker/dev/Dockerfile
        │   ├── docker/dev/php.ini
        │   └── .env.example
        ├── frontend/
        │   ├── docker/dev/Dockerfile
        │   └── .env.example
        ├── .claude/settings.json
        ├── CLAUDE.md
        ├── compose.yml
        ├── compose.prod.yml
        ├── .env.example
        └── .gitignore
```

---

## Task 1: Initialize the project (root package.json)

**Files:**
- Create: `package.json`
- Create: `tsconfig.json`
- Create: `cli/src/templates.ts` (placeholder, filled in Task 2)

The entire project lives under one `package.json` at the root. The CLI entry point is `cli/src/index.ts`; the scripts live in `scripts/`.

- [ ] **Step 1: Create root `package.json`**

```json
{
  "name": "innertia-setup",
  "version": "0.1.0",
  "description": "Innertia stack project scaffolder",
  "type": "module",
  "bin": {
    "innertia-setup": "./dist/cli/index.js"
  },
  "scripts": {
    "dev": "tsx cli/src/index.ts",
    "build": "tsup cli/src/index.ts --format esm --dts --clean --out-dir dist/cli",
    "test": "vitest run",
    "test:watch": "vitest",
    "create": "tsx scripts/create.ts"
  },
  "dependencies": {
    "@clack/prompts": "^0.9.0",
    "fast-glob": "^3.3.2",
    "fs-extra": "^11.2.0"
  },
  "devDependencies": {
    "@types/fs-extra": "^11.0.4",
    "@types/node": "^20.0.0",
    "tsup": "^8.0.0",
    "tsx": "^4.0.0",
    "typescript": "^5.0.0",
    "vitest": "^1.0.0"
  }
}
```

- [ ] **Step 2: Create `tsconfig.json`**

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true
  },
  "include": ["cli/src", "scripts"]
}
```

- [ ] **Step 3: Install dependencies**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm install
```

Expected: `node_modules/` created, no errors.

- [ ] **Step 4: Commit**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
git init
git add package.json tsconfig.json
git commit -m "chore: initialize project"
```

---

## Task 2: Template registry

**Files:**
- Create: `cli/src/templates.ts`

- [ ] **Step 1: Create `cli/src/templates.ts`**

```typescript
export interface Template {
  id: string
  label: string
  description: string
}

export const templates: Template[] = [
  {
    id: 'nuxt-landing',
    label: 'Nuxt Landing',
    description: 'Standalone Nuxt site for a landing page',
  },
  {
    id: 'laravel-api',
    label: 'Laravel API',
    description: 'Standalone Laravel REST API with Docker',
  },
  {
    id: 'app',
    label: 'App (Laravel + Nuxt)',
    description: 'Monorepo with Laravel backend + Nuxt frontend + Docker Compose',
  },
  {
    id: 'saas',
    label: 'SaaS (Laravel + Nuxt + Multitenancy)',
    description: 'Monorepo with multitenancy, Laravel + Nuxt + Docker Compose',
  },
]
```

- [ ] **Step 2: Commit**

```bash
git add cli/src/templates.ts
git commit -m "feat: template registry"
```

---

## Task 3: Variable substitution engine

**Files:**
- Create: `scripts/scaffold.ts`
- Create: `scripts/__tests__/scaffold.test.ts`

- [ ] **Step 1: Write the failing tests**

Create `scripts/__tests__/scaffold.test.ts`:

```typescript
import { describe, it, expect } from 'vitest'
import { substituteVariables, buildVars } from '../scaffold.js'

describe('substituteVariables', () => {
  it('replaces a single placeholder', () => {
    const result = substituteVariables('Hello {{PROJECT_NAME}}!', {
      PROJECT_NAME: 'pomely',
    })
    expect(result).toBe('Hello pomely!')
  })

  it('replaces multiple occurrences', () => {
    const result = substituteVariables(
      'DB_DATABASE={{PROJECT_NAME}}\nDB_PASSWORD={{PROJECT_NAME}}',
      { PROJECT_NAME: 'pomely' }
    )
    expect(result).toBe('DB_DATABASE=pomely\nDB_PASSWORD=pomely')
  })

  it('replaces all defined placeholders', () => {
    const result = substituteVariables(
      '{{PROJECT_NAME}} {{PROJECT_NAME_UPPER}}',
      { PROJECT_NAME: 'pomely', PROJECT_NAME_UPPER: 'POMELY' }
    )
    expect(result).toBe('pomely POMELY')
  })

  it('leaves unknown placeholders untouched', () => {
    const result = substituteVariables('{{UNKNOWN}}', { PROJECT_NAME: 'pomely' })
    expect(result).toBe('{{UNKNOWN}}')
  })
})

describe('buildVars', () => {
  it('derives PROJECT_NAME_UPPER from project name', () => {
    const vars = buildVars('pomely')
    expect(vars.PROJECT_NAME).toBe('pomely')
    expect(vars.PROJECT_NAME_UPPER).toBe('POMELY')
  })

  it('sets DB_PASSWORD equal to project name', () => {
    const vars = buildVars('pomely')
    expect(vars.DB_PASSWORD).toBe('pomely')
  })

  it('uses fixed port values', () => {
    const vars = buildVars('any-project')
    expect(vars.APP_PORT).toBe('8100')
    expect(vars.DB_PORT).toBe('5437')
    expect(vars.REDIS_PORT).toBe('63791')
    expect(vars.FRONTEND_PORT).toBe('3000')
  })
})
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: FAIL — `Cannot find module '../scaffold.js'`

- [ ] **Step 3: Implement `substituteVariables` and `buildVars` in `scripts/scaffold.ts`**

```typescript
export type Vars = Record<string, string>

export function substituteVariables(content: string, vars: Vars): string {
  return content.replace(/\{\{([A-Z_]+)\}\}/g, (match, key) => {
    return key in vars ? vars[key] : match
  })
}

export function buildVars(projectName: string): Vars {
  return {
    PROJECT_NAME: projectName,
    PROJECT_NAME_UPPER: projectName.toUpperCase(),
    DB_PASSWORD: projectName,
    APP_PORT: '8100',
    DB_PORT: '5437',
    REDIS_PORT: '63791',
    FRONTEND_PORT: '3000',
    XDEBUG_PORT: '9003',
  }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add cli/src/scaffold.ts cli/src/__tests__/scaffold.test.ts
git commit -m "feat: variable substitution engine"
```

---

## Task 4: Scaffold — file copy, substitution, and .env generation

**Files:**
- Modify: `cli/src/scaffold.ts` (add `scaffoldProject`, `generateEnvFiles`)
- Modify: `cli/src/__tests__/scaffold.test.ts` (add tests)

- [ ] **Step 1: Add tests for `generateEnvFiles` logic**

Append to `cli/src/__tests__/scaffold.test.ts`:

```typescript
import { generateEnvContent } from '../scaffold.js'

describe('generateEnvContent', () => {
  it('returns the same content as .env.example (vars already substituted)', () => {
    const example = 'APP_NAME=pomely\nDB_PASSWORD=pomely\n'
    expect(generateEnvContent(example)).toBe(example)
  })
})
```

- [ ] **Step 2: Run tests to verify the new one fails**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: FAIL — `generateEnvContent is not a function`

- [ ] **Step 3: Implement `generateEnvContent` and `scaffoldProject` in `cli/src/scaffold.ts`**

Replace the entire file with:

```typescript
import { promises as fs } from 'fs'
import path from 'path'
import fse from 'fs-extra'
import glob from 'fast-glob'

export type Vars = Record<string, string>

const BINARY_EXTENSIONS = new Set([
  '.png', '.jpg', '.jpeg', '.gif', '.ico', '.svg',
  '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.zip',
])

function isBinary(filePath: string): boolean {
  return BINARY_EXTENSIONS.has(path.extname(filePath).toLowerCase())
}

export function substituteVariables(content: string, vars: Vars): string {
  return content.replace(/\{\{([A-Z_]+)\}\}/g, (match, key) => {
    return key in vars ? vars[key] : match
  })
}

export function buildVars(projectName: string): Vars {
  return {
    PROJECT_NAME: projectName,
    PROJECT_NAME_UPPER: projectName.toUpperCase(),
    DB_PASSWORD: projectName,
    APP_PORT: '8100',
    DB_PORT: '5437',
    REDIS_PORT: '63791',
    FRONTEND_PORT: '3000',
    XDEBUG_PORT: '9003',
  }
}

export function generateEnvContent(exampleContent: string): string {
  return exampleContent
}

export async function scaffoldProject(
  templateDir: string,
  targetDir: string,
  vars: Vars
): Promise<void> {
  await fse.copy(templateDir, targetDir)

  const files = await glob('**/*', {
    cwd: targetDir,
    dot: true,
    onlyFiles: true,
  })

  for (const file of files) {
    const filePath = path.join(targetDir, file)
    if (isBinary(filePath)) continue

    const content = await fs.readFile(filePath, 'utf-8')
    const substituted = substituteVariables(content, vars)
    await fs.writeFile(filePath, substituted, 'utf-8')
  }

  const envExamples = files.filter(f => path.basename(f) === '.env.example')
  for (const example of envExamples) {
    const examplePath = path.join(targetDir, example)
    const envPath = path.join(path.dirname(examplePath), '.env')
    const content = await fs.readFile(examplePath, 'utf-8')
    await fs.writeFile(envPath, generateEnvContent(content), 'utf-8')
  }
}
```

- [ ] **Step 4: Run all tests to verify they pass**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: all PASS

- [ ] **Step 5: Commit**

```bash
git add scripts/scaffold.ts scripts/__tests__/scaffold.test.ts
git commit -m "feat: scaffold copy, substitution, and .env generation"
```

---

## Task 5: Post-install script

**Files:**
- Create: `scripts/post-install.ts`

- [ ] **Step 1: Create `scripts/post-install.ts`**

```typescript
import { execSync } from 'child_process'

export function runPostInstall(projectDir: string): void {
  execSync('git init', { cwd: projectDir, stdio: 'pipe' })
  execSync('git add -A', { cwd: projectDir, stdio: 'pipe' })
  execSync(
    'git commit -m "chore: initial project scaffold (innertia-setup)"',
    { cwd: projectDir, stdio: 'pipe' }
  )
}
```

No unit test — thin shell wrapper. Integration validated via the smoke test in Task 11.

- [ ] **Step 2: Commit**

```bash
git add scripts/post-install.ts
git commit -m "feat: post-install script"
```

---

## Task 6: Main create script + thin CLI

**Files:**
- Create: `scripts/create.ts`
- Create: `cli/src/prompts.ts`
- Create: `cli/src/index.ts`

`scripts/create.ts` does all the work; the CLI collects answers and calls it.

- [ ] **Step 1: Create `scripts/create.ts`**

```typescript
#!/usr/bin/env node
import path from 'path'
import { fileURLToPath } from 'url'
import { scaffoldProject, buildVars } from './scaffold.js'
import { runPostInstall } from './post-install.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export async function createProject(projectName: string, templateId: string): Promise<void> {
  const templateDir = path.resolve(__dirname, '../templates', templateId)
  const targetDir = path.resolve(process.cwd(), projectName)

  const vars = buildVars(projectName)
  await scaffoldProject(templateDir, targetDir, vars)
  runPostInstall(targetDir)
}

// Allow running directly: npx tsx scripts/create.ts my-project app
if (process.argv[1] === fileURLToPath(import.meta.url)) {
  const [, , projectName, templateId] = process.argv
  if (!projectName || !templateId) {
    console.error('Usage: tsx scripts/create.ts <project-name> <template-id>')
    process.exit(1)
  }
  createProject(projectName, templateId).catch(err => {
    console.error(err)
    process.exit(1)
  })
}
```

- [ ] **Step 2: Create `cli/src/prompts.ts`**

```typescript
import * as p from '@clack/prompts'
import { templates } from './templates.js'

export interface ProjectAnswers {
  projectName: string
  templateId: string
}

export async function runPrompts(): Promise<ProjectAnswers> {
  p.intro('innertia-setup — Innertia project scaffolder')

  const projectName = await p.text({
    message: 'Project name?',
    placeholder: 'my-project',
    validate(value) {
      if (!value) return 'Project name is required'
      if (!/^[a-z0-9-]+$/.test(value))
        return 'Use only lowercase letters, numbers, and hyphens'
    },
  })

  if (p.isCancel(projectName)) {
    p.cancel('Cancelled.')
    process.exit(0)
  }

  const templateId = await p.select({
    message: 'Template?',
    options: templates.map(t => ({
      value: t.id,
      label: t.label,
      hint: t.description,
    })),
  })

  if (p.isCancel(templateId)) {
    p.cancel('Cancelled.')
    process.exit(0)
  }

  return { projectName: projectName as string, templateId: templateId as string }
}
```

- [ ] **Step 3: Create `cli/src/index.ts`**

```typescript
#!/usr/bin/env node
import * as p from '@clack/prompts'
import { runPrompts } from './prompts.js'
import { createProject } from '../../scripts/create.js'

async function main() {
  const { projectName, templateId } = await runPrompts()

  const spinner = p.spinner()

  spinner.start('Creating project...')
  await createProject(projectName, templateId)
  spinner.stop('Done.')

  p.outro(
    `Project ready at ./${projectName}\n` +
    `Next: cd ${projectName} && docker compose up`
  )
}

main().catch(err => {
  console.error(err)
  process.exit(1)
})
```

- [ ] **Step 4: Run tests to confirm nothing broke**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add scripts/create.ts cli/src/prompts.ts cli/src/index.ts
git commit -m "feat: create script and thin CLI entry point"
```

---

## Task 7: Template — `nuxt-landing`

**Files:**
- Create: `templates/nuxt-landing/.claude/settings.json`
- Create: `templates/nuxt-landing/CLAUDE.md`
- Create: `templates/nuxt-landing/docker/dev/Dockerfile`
- Create: `templates/nuxt-landing/docker/prod/Dockerfile`
- Create: `templates/nuxt-landing/compose.yml`
- Create: `templates/nuxt-landing/compose.prod.yml`
- Create: `templates/nuxt-landing/.env.example`
- Create: `templates/nuxt-landing/.gitignore`
- Create: `templates/nuxt-landing/nuxt.config.ts`

- [ ] **Step 1: Create `templates/nuxt-landing/.claude/settings.json`**

```json
{
  "permissions": {
    "allow": [
      "Bash(docker compose *)",
      "Bash(npm run *)",
      "Bash(npm install *)",
      "Bash(npx *)"
    ]
  }
}
```

- [ ] **Step 2: Create `templates/nuxt-landing/CLAUDE.md`**

```markdown
# {{PROJECT_NAME}} — Nuxt Landing

## Stack
- Nuxt 3 (SSG/SSR)
- Docker for development and production

## Commands
- `docker compose up` — start dev server
- `npm run build` — production build
- `npm run generate` — static export

## Ports
- Frontend: http://localhost:{{FRONTEND_PORT}}

## Structure
- `pages/` — file-based routing
- `components/` — reusable components
- `public/` — static assets
```

- [ ] **Step 3: Create `templates/nuxt-landing/docker/dev/Dockerfile`**

```dockerfile
FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
EXPOSE 3000
CMD ["npm", "run", "dev"]
```

- [ ] **Step 4: Create `templates/nuxt-landing/docker/prod/Dockerfile`**

```dockerfile
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM node:20-alpine
WORKDIR /app
COPY --from=builder /app/.output ./.output
EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
```

- [ ] **Step 5: Create `templates/nuxt-landing/compose.yml`**

```yaml
services:
  frontend:
    build:
      context: .
      dockerfile: docker/dev/Dockerfile
    working_dir: /app
    volumes:
      - ".:/app"
      - node_modules:/app/node_modules
    ports:
      - "{{FRONTEND_PORT}}:3000"
    env_file:
      - .env

volumes:
  node_modules:
```

- [ ] **Step 6: Create `templates/nuxt-landing/compose.prod.yml`**

```yaml
services:
  frontend:
    build:
      context: .
      dockerfile: docker/prod/Dockerfile
    restart: unless-stopped
    ports:
      - "{{FRONTEND_PORT}}:3000"
    env_file:
      - .env
```

- [ ] **Step 7: Create `templates/nuxt-landing/.env.example`**

```
NUXT_PUBLIC_SITE_URL=http://localhost:{{FRONTEND_PORT}}
```

- [ ] **Step 8: Create `templates/nuxt-landing/.gitignore`**

```
node_modules/
.nuxt/
.output/
dist/
.env
```

- [ ] **Step 9: Create `templates/nuxt-landing/nuxt.config.ts`**

```typescript
export default defineNuxtConfig({
  devtools: { enabled: true },
})
```

- [ ] **Step 10: Commit**

```bash
git add templates/nuxt-landing/
git commit -m "feat: nuxt-landing template"
```

---

## Task 8: Template — `laravel-api`

**Files:**
- Create: `templates/laravel-api/.claude/settings.json`
- Create: `templates/laravel-api/CLAUDE.md`
- Create: `templates/laravel-api/docker/dev/Dockerfile`
- Create: `templates/laravel-api/docker/dev/php.ini`
- Create: `templates/laravel-api/docker/prod/Dockerfile`
- Create: `templates/laravel-api/compose.yml`
- Create: `templates/laravel-api/compose.prod.yml`
- Create: `templates/laravel-api/.env.example`
- Create: `templates/laravel-api/.gitignore`

- [ ] **Step 1: Create `templates/laravel-api/.claude/settings.json`**

```json
{
  "permissions": {
    "allow": [
      "Bash(php artisan *)",
      "Bash(composer *)",
      "Bash(docker compose *)"
    ]
  }
}
```

- [ ] **Step 2: Create `templates/laravel-api/CLAUDE.md`**

```markdown
# {{PROJECT_NAME}} — Laravel API

## Stack
- Laravel 11
- PostgreSQL
- Docker + Xdebug

## Commands
- `docker compose up` — start all services
- `docker compose exec api php artisan migrate` — run migrations
- `docker compose exec api php artisan tinker` — REPL

## Ports
- API: http://localhost:{{APP_PORT}}
- DB: localhost:{{DB_PORT}}
```

- [ ] **Step 3: Create `templates/laravel-api/docker/dev/Dockerfile`**

```dockerfile
FROM php:8.3-apache
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

- [ ] **Step 4: Create `templates/laravel-api/docker/dev/php.ini`**

```ini
[xdebug]
xdebug.mode=debug
xdebug.client_host=host.docker.internal
xdebug.client_port={{XDEBUG_PORT}}
xdebug.start_with_request=yes
xdebug.idekey=vscode
```

- [ ] **Step 5: Create `templates/laravel-api/docker/prod/Dockerfile`**

```dockerfile
FROM php:8.3-apache
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql zip opcache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache
```

- [ ] **Step 6: Create `templates/laravel-api/compose.yml`**

```yaml
services:
  api:
    build:
      context: .
      dockerfile: docker/dev/Dockerfile
    working_dir: /var/www/html
    extra_hosts:
      - "host.docker.internal:host-gateway"
    volumes:
      - ".:/var/www/html"
      - "./docker/dev/php.ini:/usr/local/etc/php/php.ini"
    ports:
      - "{{APP_PORT}}:80"
      - "{{XDEBUG_PORT}}:{{XDEBUG_PORT}}"
    environment:
      APP_ENV: "local"
      APP_DEBUG: "true"
      DB_HOST: database
      DB_PORT: 5432
      DB_DATABASE: {{PROJECT_NAME}}
      DB_USERNAME: root
      DB_PASSWORD: {{DB_PASSWORD}}
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal client_port={{XDEBUG_PORT}} start_with_request=yes idekey=vscode
      XDEBUG_LOG: /tmp/xdebug.log
      XDEBUG_LOG_LEVEL: "0"
    env_file:
      - .env

  database:
    image: postgres:16
    platform: linux/amd64
    volumes:
      - postgres:/var/lib/postgresql/data
    environment:
      POSTGRES_USER: root
      POSTGRES_PASSWORD: {{DB_PASSWORD}}
      POSTGRES_DB: {{PROJECT_NAME}}
    ports:
      - "{{DB_PORT}}:5432"
    mem_limit: 256m
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U root -d {{PROJECT_NAME}}"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 10s

  redis:
    image: redis:7-alpine
    ports:
      - "{{REDIS_PORT}}:6379"
    volumes:
      - redis:/data
    command: redis-server --appendonly yes

volumes:
  postgres:
  redis:
```

- [ ] **Step 7: Create `templates/laravel-api/compose.prod.yml`**

```yaml
services:
  api:
    build:
      context: .
      dockerfile: docker/prod/Dockerfile
    restart: unless-stopped
    environment:
      APP_ENV: "production"
      APP_DEBUG: "false"

  database:
    restart: unless-stopped

  redis:
    restart: unless-stopped
```

- [ ] **Step 8: Create `templates/laravel-api/.env.example`**

```
APP_NAME={{PROJECT_NAME}}
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:{{APP_PORT}}

DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE={{PROJECT_NAME}}
DB_USERNAME=root
DB_PASSWORD={{DB_PASSWORD}}

CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

- [ ] **Step 9: Create `templates/laravel-api/.gitignore`**

```
vendor/
.env
storage/logs/*.log
bootstrap/cache/
```

- [ ] **Step 10: Commit**

```bash
git add templates/laravel-api/
git commit -m "feat: laravel-api template"
```

---

## Task 9: Template — `app` (Laravel + Nuxt monorepo)

**Files:**
- Create: `templates/app/compose.yml`
- Create: `templates/app/compose.prod.yml`
- Create: `templates/app/.env.example`
- Create: `templates/app/.gitignore`
- Create: `templates/app/CLAUDE.md`
- Create: `templates/app/.claude/settings.json`
- Create: `templates/app/backend/docker/dev/Dockerfile`
- Create: `templates/app/backend/docker/dev/php.ini`
- Create: `templates/app/backend/.env.example`
- Create: `templates/app/frontend/docker/dev/Dockerfile`
- Create: `templates/app/frontend/.env.example`

- [ ] **Step 1: Create `templates/app/compose.yml`**

```yaml
services:
  api:
    build:
      context: ./backend
      dockerfile: docker/dev/Dockerfile
    working_dir: /var/www/html
    extra_hosts:
      - "host.docker.internal:host-gateway"
    volumes:
      - "./backend:/var/www/html"
      - "./backend/docker/dev/php.ini:/usr/local/etc/php/php.ini"
    ports:
      - "{{APP_PORT}}:80"
      - "{{XDEBUG_PORT}}:{{XDEBUG_PORT}}"
    environment:
      APP_ENV: "local"
      APP_DEBUG: "true"
      DB_HOST: database
      DB_PORT: 5432
      DB_DATABASE: {{PROJECT_NAME}}
      DB_USERNAME: root
      DB_PASSWORD: {{DB_PASSWORD}}
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal client_port={{XDEBUG_PORT}} start_with_request=yes idekey=vscode
      XDEBUG_LOG: /tmp/xdebug.log
      XDEBUG_LOG_LEVEL: "0"
    env_file:
      - .env

  frontend:
    build:
      context: ./frontend
      dockerfile: docker/dev/Dockerfile
    working_dir: /app
    volumes:
      - "./frontend:/app"
      - frontend_node_modules:/app/node_modules
    ports:
      - "{{FRONTEND_PORT}}:3000"
    env_file:
      - .env
    depends_on:
      - api

  database:
    image: postgres:16
    platform: linux/amd64
    volumes:
      - postgres:/var/lib/postgresql/data
    environment:
      POSTGRES_USER: root
      POSTGRES_PASSWORD: {{DB_PASSWORD}}
      POSTGRES_DB: {{PROJECT_NAME}}
    ports:
      - "{{DB_PORT}}:5432"
    mem_limit: 256m
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U root -d {{PROJECT_NAME}}"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 10s

  redis:
    image: redis:7-alpine
    ports:
      - "{{REDIS_PORT}}:6379"
    volumes:
      - redis:/data
    command: redis-server --appendonly yes
    healthcheck:
      test: ["CMD-SHELL", "redis-cli ping"]
      interval: 5s
      timeout: 3s
      retries: 5

volumes:
  postgres:
  redis:
  frontend_node_modules:
```

- [ ] **Step 2: Create `templates/app/compose.prod.yml`**

```yaml
services:
  api:
    restart: unless-stopped
    environment:
      APP_ENV: "production"
      APP_DEBUG: "false"

  frontend:
    restart: unless-stopped

  database:
    restart: unless-stopped

  redis:
    restart: unless-stopped
```

- [ ] **Step 3: Create `templates/app/.env.example`**

```
APP_PORT={{APP_PORT}}
DB_PORT={{DB_PORT}}
REDIS_PORT={{REDIS_PORT}}
FRONTEND_PORT={{FRONTEND_PORT}}
```

- [ ] **Step 4: Create `templates/app/.gitignore`**

```
.env
backend/vendor/
backend/storage/logs/*.log
backend/bootstrap/cache/
frontend/node_modules/
frontend/.nuxt/
frontend/.output/
```

- [ ] **Step 5: Create `templates/app/CLAUDE.md`**

```markdown
# {{PROJECT_NAME}} — App (Laravel + Nuxt)

## Stack
- Laravel 11 (backend/) — REST API
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL, Redis
- Docker Compose

## Commands
- `docker compose up` — start all services
- `docker compose exec api php artisan migrate`
- `docker compose exec api php artisan tinker`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture
- backend/ is a standard Laravel app. API routes in routes/api.php.
- frontend/ is a Nuxt 3 app consuming the API via NUXT_PUBLIC_API_URL.
- Both run in Docker; connect via service names (api, database, redis).
```

- [ ] **Step 6: Create `templates/app/.claude/settings.json`**

```json
{
  "permissions": {
    "allow": [
      "Bash(docker compose *)",
      "Bash(php artisan *)",
      "Bash(composer *)",
      "Bash(npm run *)",
      "Bash(npm install *)"
    ]
  }
}
```

- [ ] **Step 7: Create `templates/app/backend/docker/dev/Dockerfile`**

```dockerfile
FROM php:8.3-apache
RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

- [ ] **Step 8: Create `templates/app/backend/docker/dev/php.ini`**

```ini
[xdebug]
xdebug.mode=debug
xdebug.client_host=host.docker.internal
xdebug.client_port={{XDEBUG_PORT}}
xdebug.start_with_request=yes
xdebug.idekey=vscode
```

- [ ] **Step 9: Create `templates/app/backend/.env.example`**

```
APP_NAME={{PROJECT_NAME}}
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:{{APP_PORT}}

DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE={{PROJECT_NAME}}
DB_USERNAME=root
DB_PASSWORD={{DB_PASSWORD}}

CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379

BROADCAST_CONNECTION=log
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

- [ ] **Step 10: Create `templates/app/frontend/docker/dev/Dockerfile`**

```dockerfile
FROM node:20-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
EXPOSE 3000
CMD ["npm", "run", "dev"]
```

- [ ] **Step 11: Create `templates/app/frontend/.env.example`**

```
NUXT_PUBLIC_API_URL=http://localhost:{{APP_PORT}}
```

- [ ] **Step 12: Commit**

```bash
git add templates/app/
git commit -m "feat: app template (Laravel + Nuxt monorepo)"
```

---

## Task 10: Template — `saas` (Laravel + Nuxt + multitenancy)

**Files:** Same structure as `app/` with multitenancy-specific notes.

- [ ] **Step 1: Copy `app` template as base for `saas`**

```bash
cp -r /Users/guillermofarias/Sites/innertia-setup/templates/app \
       /Users/guillermofarias/Sites/innertia-setup/templates/saas
```

- [ ] **Step 2: Update `templates/saas/CLAUDE.md`** to reflect multitenancy

Replace content with:

```markdown
# {{PROJECT_NAME}} — SaaS (Laravel + Nuxt + Multitenancy)

## Stack
- Laravel 11 (backend/) — REST API with multitenancy via `stancl/tenancy`
- Nuxt 3 (frontend/) — SPA/SSR
- PostgreSQL (central DB + per-tenant DBs), Redis
- Docker Compose

## Commands
- `docker compose up` — start all services
- `docker compose exec api php artisan migrate` — central DB migrations
- `docker compose exec api php artisan tenants:migrate` — tenant DB migrations
- `docker compose exec api php artisan tinker`

## Ports
- API: http://localhost:{{APP_PORT}}
- Frontend: http://localhost:{{FRONTEND_PORT}}
- DB: localhost:{{DB_PORT}}
- Redis: localhost:{{REDIS_PORT}}

## Architecture
- Multitenancy via `stancl/tenancy` (single DB with tenant scoping, or multiple DBs — configured in config/tenancy.php).
- Central routes in routes/api.php. Tenant routes in routes/tenant.php.
- frontend/ is a Nuxt 3 app consuming the API.
- Tenants are identified by subdomain or header (configured per project).
```

- [ ] **Step 3: Update `templates/saas/backend/.env.example`** to add tenancy vars

```
APP_NAME={{PROJECT_NAME}}
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:{{APP_PORT}}

DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE={{PROJECT_NAME}}
DB_USERNAME=root
DB_PASSWORD={{DB_PASSWORD}}

CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PORT=6379

BROADCAST_CONNECTION=log
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

TENANCY_ENABLED=true
```

- [ ] **Step 4: Commit**

```bash
git add templates/saas/
git commit -m "feat: saas template (Laravel + Nuxt + multitenancy)"
```

---

## Task 11: Build and smoke test

**Files:** None new — validates everything works end-to-end.

- [ ] **Step 1: Build the CLI**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm run build
```

Expected: `dist/index.js` created, no errors.

- [ ] **Step 2: Link the CLI globally**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm link
```

Expected: `innertia-setup` command available globally.

- [ ] **Step 3: Run smoke test — `app` template**

```bash
cd /tmp
innertia-setup
# → enter: "smoke-test-app"
# → select: app
```

Expected:
- `/tmp/smoke-test-app/` created
- `compose.yml` contains `smoke-test-app` (not `{{PROJECT_NAME}}`)
- `backend/.env` exists and has `APP_NAME=smoke-test-app`
- `frontend/.env` exists
- `.env` exists at root
- `CLAUDE.md` contains `smoke-test-app`
- `.git/` exists with one commit

- [ ] **Step 4: Verify substitution manually**

```bash
grep -r "{{" /tmp/smoke-test-app/ --include="*.yml" --include="*.env" --include="*.md"
```

Expected: no output (no unsubstituted placeholders).

- [ ] **Step 5: Cleanup and run smoke test — `nuxt-landing`**

```bash
rm -rf /tmp/smoke-test-app
cd /tmp
innertia-setup
# → enter: "smoke-test-landing"
# → select: nuxt-landing
```

Expected: `/tmp/smoke-test-landing/` with `.env`, `CLAUDE.md`, `.git/`.

- [ ] **Step 6: Cleanup**

```bash
rm -rf /tmp/smoke-test-app /tmp/smoke-test-landing
```

- [ ] **Step 7: Run full test suite one last time**

```bash
cd /Users/guillermofarias/Sites/inertia/innertia-setup
npm test
```

Expected: all PASS

- [ ] **Step 8: Commit**

```bash
cd /Users/guillermofarias/Sites/innertia-setup
git add cli/dist/
git commit -m "chore: build dist"
```

---

## Task 12: Root project setup

**Files:**
- Create: `README.md`
- Create: `.gitignore`

- [ ] **Step 1: Create root `.gitignore`**

```
cli/node_modules/
cli/dist/
```

- [ ] **Step 2: Create `README.md`**

```markdown
# innertia-setup

Interactive CLI scaffolder for the Innertia stack (Laravel + Nuxt + Docker).

## Usage

```bash
npx innertia-setup
# or during development:
cd cli && npm link && innertia-setup
```

## Templates

| Template | Description |
|---|---|
| `nuxt-landing` | Standalone Nuxt site |
| `laravel-api` | Standalone Laravel API |
| `app` | Laravel + Nuxt monorepo |
| `saas` | Laravel + Nuxt + multitenancy |

## Development

```bash
cd cli
npm install
npm run dev      # run without building
npm test         # run tests
npm run build    # build to dist/
```

## Adding a template

1. Create `templates/<name>/` with the template files
2. Add the entry to `cli/src/templates.ts`
3. Use `{{PLACEHOLDER}}` for substituted values (see Variable Substitution in the spec)
```

- [ ] **Step 3: Commit**

```bash
cd /Users/guillermofarias/Sites/innertia-setup
git add README.md .gitignore
git commit -m "chore: root project setup"
```
