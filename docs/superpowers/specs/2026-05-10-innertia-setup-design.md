# innertia-setup — Design Spec

**Date:** 2026-05-10
**Status:** Approved

## Overview

`innertia-setup` is a personal project scaffolding CLI for the Innertia stack (Laravel + Nuxt + Docker). It provides an interactive installer that generates fully configured project boilerplates — including Docker Compose orchestration, environment files, and AI rules — from a single command.

## Goals

- Create new projects in seconds with a guided interactive flow
- Enforce consistency across all Innertia projects (same Docker patterns, same AI rules)
- Start with clean installs of Laravel/Nuxt; evolve templates into full boilerplates over time
- Support future Python backends by adding new templates

## Non-Goals

- Team/public distribution (personal tool, though built solidly)
- Remote template fetching (all templates are local to the repo)
- Updating existing projects

---

## Architecture

### Repository Structure

```
innertia-setup/
├── cli/
│   ├── src/
│   │   ├── index.ts          # entry point — orchestrates the full install flow
│   │   ├── prompts.ts        # all interactive questions (clack)
│   │   ├── scaffold.ts       # copies template, substitutes variables
│   │   ├── post-install.ts   # post-install hooks (git init, first commit)
│   │   └── templates.ts      # template registry and metadata
│   ├── package.json
│   └── tsconfig.json
│
├── templates/
│   ├── nuxt-landing/         # standalone Nuxt site
│   ├── laravel-api/          # standalone Laravel API
│   ├── app/                  # monorepo: Laravel + Nuxt + Docker Compose
│   └── saas/                 # monorepo: Laravel + Nuxt + multitenancy + Docker Compose
│
└── README.md
```

### Template Structure (monorepo templates: `app`, `saas`)

```
templates/app/
├── backend/
│   ├── docker/dev/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── .env.example
├── frontend/
│   ├── docker/dev/
│   │   └── Dockerfile
│   └── .env.example
├── .claude/
│   └── settings.json         # AI tool permissions for this stack
├── CLAUDE.md                 # AI rules and conventions for this project type
├── compose.yml               # Docker Compose with {{PLACEHOLDERS}}
├── compose.prod.yml
├── .env.example              # monorepo-root env vars
└── .gitignore
```

Single-service templates (`nuxt-landing`, `laravel-api`) follow the same pattern without the monorepo wrapper.

---

## CLI Flow

```
npx innertia-setup   (or: npm link → innertia-setup)

  → ◆ Project name?           [my-project]
  → ◆ Template?               [nuxt-landing / laravel-api / app / saas]

  ◇ Copying template...
  ◇ Substituting variables...
  ◇ Generating .env files...
  ◇ Installing AI rules...
  ◇ git init + initial commit...

  ✓ Project ready at ./my-project
  ✓ Next: cd my-project && docker compose up
```

Only two prompts: project name and template type. Ports and other infrastructure values are fixed — one project runs at a time.

---

## Variable Substitution

Placeholders in template files use `{{VARIABLE_NAME}}` syntax. The CLI performs a find-and-replace across all text files in the copied template.

| Placeholder | Source | Value |
|---|---|---|
| `{{PROJECT_NAME}}` | user input | `pomely` |
| `{{PROJECT_NAME_UPPER}}` | derived | `POMELY` |
| `{{DB_PASSWORD}}` | derived from project name | `pomely` |
| `{{APP_PORT}}` | fixed | `8100` |
| `{{DB_PORT}}` | fixed | `5437` |
| `{{REDIS_PORT}}` | fixed | `6379` |
| `{{FRONTEND_PORT}}` | fixed | `3000` |

Substitution applies to: `compose.yml`, `compose.prod.yml`, `.env.example`, `CLAUDE.md`, `Dockerfile`s, and any other text file in the template.

---

## .env Generation

After substitution, the CLI generates `.env` from each `.env.example` in the project tree:
- Root `.env.example` → `.env`
- `backend/.env.example` → `backend/.env`
- `frontend/.env.example` → `frontend/.env`

The generated `.env` files are identical to their `.env.example` counterparts (with placeholders already substituted). The user starts with working local defaults.

---

## AI Rules Integration

Each template ships with its own AI rules tailored to its stack:

- `CLAUDE.md` — project conventions, architecture notes, commands reference
- `.claude/settings.json` — tool permissions (allowed bash commands, MCP servers, etc.)

These are copied as part of the template and are immediately active when the user opens Claude Code in the new project. No manual setup required.

The `saas` and `app` templates include rules for both the Laravel backend and Nuxt frontend. Single-service templates include only their relevant rules.

---

## Post-Install Hooks

After scaffolding, the CLI runs:

1. `git init` in the project root
2. Stage all files
3. Commit: `chore: initial project scaffold (innertia-setup)`

---

## Tech Stack

| Concern | Choice | Reason |
|---|---|---|
| Language | TypeScript | Consistency with Nuxt ecosystem |
| Prompts | `clack` | Polished interactive UX, same feel as Laravel installer |
| File ops | `fs-extra` | Recursive copy with ease |
| File scanning | `fast-glob` | Find all template files for variable substitution |
| Build | `tsup` | Zero-config TS bundler |
| Dev runner | `tsx` | Run TS directly without build step |

### package.json (cli)

```json
{
  "name": "innertia-setup",
  "version": "0.1.0",
  "bin": { "innertia-setup": "./dist/index.js" },
  "scripts": {
    "dev": "tsx src/index.ts",
    "build": "tsup src/index.ts --format esm --dts"
  }
}
```

During development: `npm link` inside `cli/` makes `innertia-setup` available globally.
Eventually: `npx innertia-setup` once published to npm.

---

## Templates Roadmap

| Template | v0 (now) | v1 (later) |
|---|---|---|
| `nuxt-landing` | Clean `npx nuxi init` | + Tailwind, SEO setup, innertia-ui-kit |
| `laravel-api` | Clean `composer create-project` | + Sanctum, base structure, tests |
| `app` | Clean Laravel + Nuxt | + Auth, roles, base components |
| `saas` | Clean Laravel + Nuxt | + Multitenancy (tenancy for Laravel), billing |
| `python-api` | — | Future: FastAPI + Docker |
