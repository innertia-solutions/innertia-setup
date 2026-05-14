// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  extends: [
    '@innertia-solutions/nuxt-theme-spark',   // UI: Spark components and layouts
    '@innertia-solutions/nuxt-saas',    // Multi-tenant: subdomain detection, tenant validation, X-Tenant-Id
  ],
  modules: [
    '@pinia/nuxt',
    'pinia-plugin-persistedstate/nuxt',
  ],
  compatibilityDate: '2025-07-15',
  ssr: true,
  devServer: {
    host: '0.0.0.0'
  },
  runtimeConfig: {
    // Privado — solo SSR. Apunta directamente al backend sin pasar por el proxy Nitro.
    apiInternalUrl: process.env.NUXT_API_BASE_URL || 'http://api:80',
    public: {
      appName: '{{PROJECT_NAME}}',
      apiBaseUrl: process.env.NUXT_PUBLIC_API_URL || '/api',
      appEnv: process.env.NUXT_PUBLIC_APP_ENV || 'local',
      loginPath: process.env.NUXT_PUBLIC_LOGIN_PATH || '/login',
      homePath: process.env.NUXT_PUBLIC_HOME_PATH || '/',
      timeZone: process.env.NUXT_PUBLIC_TIMEZONE || 'America/Santiago',
    }
  },
  nitro: {
    routeRules: {
      '/api/**': {
        proxy: `${process.env.NUXT_API_BASE_URL || 'http://api:80'}/**`,
      },
    },
  },
  devtools: { enabled: false },
  app: {
    head: {
      title: '{{PROJECT_NAME}}',
      link: [
        { rel: 'icon', href: '/favicon.ico' },
      ],
    },
  },
})
