// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: true,
  devServer: {
    host: '0.0.0.0'
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
