import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  extends: [
    '@innertia-solutions/spark',        // UI: Spark components, layouts, Preline, Tailwind
    '@innertia-solutions/nuxt-app',     // Auth, context, permissions, useApi
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
  vite: {
    plugins: [tailwindcss()],
  },
  runtimeConfig: {
    public: {
      appName: '{{PROJECT_NAME}}',
      apiBaseUrl: process.env.NUXT_PUBLIC_API_URL || '/api',
      appEnv: process.env.NUXT_PUBLIC_APP_ENV || 'local',
      loginPath: process.env.NUXT_PUBLIC_LOGIN_PATH || '/login',
      homePath: process.env.NUXT_PUBLIC_HOME_PATH || '/',
      oauthProviders: [],
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
