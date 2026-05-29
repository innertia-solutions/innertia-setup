// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  extends: [
    '@innertia-solutions/innertia-nuxt', // Capa unificada: core + auth + design system
  ],
  css: [
    '@innertia-solutions/innertia-nuxt/theme.css',
    '~/assets/css/theme.css',
  ],
  appConfig: {
    innertia: {
      mode: 'app',
      branding: { name: '{{PROJECT_NAME}}', version: '1.0.0' },
      theme: 'default',
      colors: { primary: 'violet', secondary: 'slate' },
    },
  },
  compatibilityDate: '2025-07-15',
  ssr: true,
  devServer: {
    host: '0.0.0.0',
  },
  runtimeConfig: {
    apiInternalUrl: process.env.NUXT_API_BASE_URL || 'http://api:80',
    public: {
      appName: '{{PROJECT_NAME}}',
      apiBaseUrl: process.env.NUXT_PUBLIC_API_URL || '/api',
      appEnv: process.env.NUXT_PUBLIC_APP_ENV || 'local',
      timeZone: process.env.NUXT_PUBLIC_TIMEZONE || 'America/Santiago',
    },
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
        { rel: 'icon', type: 'image/png', href: '/favicon.png' },
      ],
    },
  },
})
