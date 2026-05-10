// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: true,
  devServer: {
    host: '0.0.0.0'
  },
  runtimeConfig: {
    public: {
      appName: '{{PROJECT_NAME}}',
      apiBaseUrl: process.env.NUXT_PUBLIC_API_URL || '/api',
      appEnv: process.env.NUXT_PUBLIC_APP_ENV || 'local',
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
