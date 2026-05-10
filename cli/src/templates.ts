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
