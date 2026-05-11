export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()

  const baseURL = config.public.apiBaseUrl as string

  function getHeaders(): Record<string, string> {
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    }
    if (authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`
    }
    return headers
  }

  async function get<T = unknown>(path: string): Promise<{ data: T | null; error: unknown }> {
    try {
      const data = await $fetch<T>(path, {
        method: 'GET',
        baseURL,
        headers: getHeaders(),
      })
      return { data, error: null }
    } catch (error) {
      return { data: null, error }
    }
  }

  async function post<T = unknown>(path: string, body?: unknown): Promise<{ data: T | null; error: unknown }> {
    try {
      const data = await $fetch<T>(path, {
        method: 'POST',
        baseURL,
        headers: getHeaders(),
        body,
      })
      return { data, error: null }
    } catch (error) {
      return { data: null, error }
    }
  }

  async function put<T = unknown>(path: string, body?: unknown): Promise<{ data: T | null; error: unknown }> {
    try {
      const data = await $fetch<T>(path, {
        method: 'PUT',
        baseURL,
        headers: getHeaders(),
        body,
      })
      return { data, error: null }
    } catch (error) {
      return { data: null, error }
    }
  }

  async function del<T = unknown>(path: string): Promise<{ data: T | null; error: unknown }> {
    try {
      const data = await $fetch<T>(path, {
        method: 'DELETE',
        baseURL,
        headers: getHeaders(),
      })
      return { data, error: null }
    } catch (error) {
      return { data: null, error }
    }
  }

  return { get, post, put, del }
}
