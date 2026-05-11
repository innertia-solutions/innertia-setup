interface LoginResponse {
  token?: string
  user?: {
    id: string
    name: string
    email: string
    roles?: string[]
  }
  requires_otp?: boolean
  requires_2fa?: boolean
  user_id?: string
  message?: string
}

interface LoginResult {
  success: boolean
  challenge: boolean
  userId?: string
  error?: string
}

export function useAuth() {
  const api = useApi()
  const authStore = useAuthStore()

  const user = computed(() => authStore.user)
  const isAuthenticated = computed(() => authStore.isAuthenticated)

  async function login(email: string, password: string, app = 'backoffice'): Promise<LoginResult> {
    const { data, error } = await api.post<LoginResponse>('/api/auth/login', {
      email,
      password,
      app,
    })

    if (error || !data) {
      const errMessage =
        (error as { data?: { message?: string } })?.data?.message ||
        'Error al iniciar sesión. Por favor, intenta de nuevo.'
      return { success: false, challenge: false, error: errMessage }
    }

    // OTP / 2FA challenge
    if (data.requires_otp || data.requires_2fa) {
      return { success: false, challenge: true, userId: data.user_id }
    }

    // Direct login
    if (data.token && data.user) {
      authStore.setToken(data.token)
      authStore.setUser({
        id: data.user.id,
        name: data.user.name,
        email: data.user.email,
        roles: data.user.roles,
      })
      return { success: true, challenge: false }
    }

    return { success: false, challenge: false, error: 'Respuesta inesperada del servidor.' }
  }

  async function logout(): Promise<void> {
    await api.post('/api/auth/logout')
    authStore.clear()
    await navigateTo('/login')
  }

  async function fetchMe(): Promise<void> {
    const { data } = await api.get<{ user: { id: string; name: string; email: string; roles?: string[] } }>(
      '/api/auth/me',
    )
    if (data?.user) {
      authStore.setUser({
        id: data.user.id,
        name: data.user.name,
        email: data.user.email,
        roles: data.user.roles,
      })
    }
  }

  return {
    user,
    isAuthenticated,
    login,
    logout,
    fetchMe,
  }
}
