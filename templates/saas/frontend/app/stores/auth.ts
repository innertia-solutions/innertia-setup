import { defineStore } from 'pinia'

export interface AuthUser {
  id: string
  name: string
  email: string
  roles?: string[]
}

export const useAuthStore = defineStore('auth', () => {
  const token = useCookie<string | null>('auth_token', {
    default: () => null,
    httpOnly: false,
    sameSite: 'lax',
    maxAge: 60 * 60 * 24 * 30, // 30 days
  })

  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function setToken(newToken: string) {
    token.value = newToken
  }

  function setUser(newUser: AuthUser) {
    user.value = newUser
  }

  function clear() {
    token.value = null
    user.value = null
  }

  return {
    token,
    user,
    isAuthenticated,
    setToken,
    setUser,
    clear,
  }
})
