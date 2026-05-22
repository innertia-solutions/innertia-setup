export default defineNuxtPlugin(() => {
  const cookie = useCookie<'dark' | 'light'>('theme', {
    default: () => 'light',
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 365,
  })

  useHead({
    htmlAttrs: {
      class: computed(() => cookie.value === 'dark' ? 'dark' : ''),
    },
  })
})
