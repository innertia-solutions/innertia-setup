<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const api = useApi()
const route = useRoute()

const sessionToken = computed(() => route.query.session_token || '')
const code = ref('')
const processing = ref(false)
const resending = ref(false)
const error = ref('')
const resent = ref(false)

async function handleSubmit() {
  if (!code.value) {
    error.value = 'Ingresa el código recibido.'
    return
  }

  error.value = ''
  processing.value = true
  try {
    const data = await api.post('backoffice/auth/otp/verify', {
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

async function resend() {
  resending.value = true
  resent.value = false
  try {
    await api.post('backoffice/auth/otp/send', { session_token: sessionToken.value })
    resent.value = true
  } catch {
    // silencioso
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
        Verificación por código
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Te enviamos un código de verificación. Ingrésalo para continuar.
      </p>
    </div>

    <div v-if="resent" class="p-3 bg-green-50 dark:bg-green-500/10 rounded-lg">
      <p class="text-sm text-green-700 dark:text-green-300">Código reenviado correctamente.</p>
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
          :disabled="processing"
          placeholder="••••••"
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

    <div class="flex items-center justify-between text-sm">
      <NuxtLink
        to="/backoffice/login"
        class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 hover:underline"
      >
        ← Volver al inicio
      </NuxtLink>
      <button
        type="button"
        :disabled="resending"
        @click="resend"
        class="text-blue-600 hover:underline dark:text-blue-400 disabled:opacity-50"
      >
        {{ resending ? 'Enviando...' : 'Reenviar código' }}
      </button>
    </div>
  </div>
</template>
