<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const api = useApi()
const route = useRoute()

const sessionToken = computed(() => route.query.session_token || '')
const code = ref('')
const processing = ref(false)
const error = ref('')

async function handleSubmit() {
  if (!code.value || code.value.length !== 6) {
    error.value = 'Ingresa el código de 6 dígitos.'
    return
  }

  error.value = ''
  processing.value = true
  try {
    const data = await api.post('backoffice/auth/2fa/verify', {
      code: code.value,
      session_token: sessionToken.value,
    })
    const authStore = useAuthStore()
    authStore.setToken(data.token)
    await navigateTo('/backoffice')
  } catch (e) {
    error.value = e?.data?.message ?? 'Código incorrecto o expirado.'
    code.value = ''
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
        Verificación en dos pasos
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Ingresa el código de tu aplicación de autenticación.
      </p>
    </div>

    <form class="space-y-4" @submit.prevent="handleSubmit" novalidate>
      <div>
        <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-white">
          Código de verificación
        </label>
        <input
          v-model="code"
          type="text"
          inputmode="numeric"
          maxlength="6"
          :disabled="processing"
          placeholder="123456"
          autocomplete="one-time-code"
          class="py-2 sm:py-2.5 px-3 block w-full border rounded-lg sm:text-sm text-center tracking-widest text-lg placeholder:text-slate-400 focus:ring-1 disabled:opacity-50 dark:bg-transparent dark:text-slate-300"
          :class="error
            ? 'border-red-400 focus:border-red-400 focus:ring-red-400'
            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:focus:ring-slate-600'"
        />
        <p v-if="error" class="mt-1.5 text-xs text-red-500">{{ error }}</p>
      </div>

      <button
        type="submit"
        :disabled="processing"
        class="py-2.5 px-3 w-full inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
      >
        <span v-if="processing" class="animate-spin inline-block size-4 border-[2px] border-t-transparent border-white rounded-full" />
        <span v-else>Verificar</span>
      </button>
    </form>

    <div class="text-center">
      <NuxtLink
        to="/backoffice/login"
        class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:underline"
      >
        ← Volver al inicio de sesión
      </NuxtLink>
    </div>
  </div>
</template>
