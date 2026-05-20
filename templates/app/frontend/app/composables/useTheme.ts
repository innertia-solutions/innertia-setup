export function useTheme() {
  const cookie = useCookie<'dark' | 'light'>('theme', {
    default: () => 'light',
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 365,
  })

  const isDark = computed(() => cookie.value === 'dark')

  const toggle = () => {
    cookie.value = cookie.value === 'dark' ? 'light' : 'dark'
  }

  return { isDark, toggle }
}
