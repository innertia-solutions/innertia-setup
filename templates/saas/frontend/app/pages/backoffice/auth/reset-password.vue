<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const api = useApi()
const route = useRoute()

const token = computed(() => route.query.token || '')
const email = computed(() => route.query.email || '')

const password = ref('')
const passwordConfirmation = ref('')
const processing = ref(false)
const success = ref(false)
const error = ref('')

const invalidLink = computed(() => !token.value || !email.value)

async function handleSubmit() {
  error.value = ''
  if (password.value !== passwordConfirmation.value) {
    error.value = 'Las contraseñas no coinciden.'
    return
  }
  if (password.value.length < 8) {
    error.value = 'La contraseña debe tener al menos 8 caracteres.'
    return
  }

  processing.value = true
  try {
    await api.post('backoffice/auth/password/reset', {
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    success.value = true
    setTimeout(() => navigateTo('/backoffice/login'), 2500)
  } catch (e) {
    error.value = e?.data?.message ?? 'El enlace es inválido o ha expirado.'
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="space-y-6">

    <!-- Enlace inválido -->
    <div v-if="invalidLink" class="flex flex-col items-center justify-center py-10 space-y-4 text-center">
      <div class="size-16 bg-red-100 dark:bg-red-500/10 rounded-full flex items-center justify-center">
        <svg class="size-8 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>
      </div>
      <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">Enlace inválido</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs">
        Este enlace de recuperación no es válido o ha expirado.
      </p>
      <NuxtLink to="/backoffice/auth/forgot-password" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
        Solicitar nuevo enlace
      </NuxtLink>
    </div>

    <!-- Éxito -->
    <div v-else-if="success" class="flex flex-col items-center justify-center py-10 space-y-4 text-center">
      <div class="size-16 bg-green-100 dark:bg-green-500/10 rounded-full flex items-center justify-center">
        <svg class="size-8 text-green-600 dark:text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <h2 class="text-xl font-bold text-slate-800 dark:text-slate-200">¡Contraseña actualizada!</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Redirigiendo al inicio de sesión...</p>
    </div>

    <!-- Formulario -->
    <template v-else>
      <div>
        <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
          Restablecer contraseña
        </h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
          Ingresa tu nueva contraseña para recuperar el acceso.
        </p>
      </div>

      <form class="space-y-4" @submit.prevent="handleSubmit" novalidate>
        <div>
          <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-white">
            Nueva contraseña
          </label>
          <div class="relative">
            <input
              id="new-password"
              v-model="password"
              type="password"
              :disabled="processing"
              placeholder="••••••••"
              class="py-2 sm:py-2.5 px-3 block w-full border border-slate-200 rounded-lg sm:text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 dark:bg-transparent dark:border-slate-700 dark:text-slate-300 dark:placeholder:text-white/60"
            />
            <button
              type="button"
              data-hs-toggle-password='{"target": "#new-password"}'
              class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer text-slate-400 rounded-e-md"
              tabindex="-1"
            >
              <svg class="shrink-0 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path class="hs-password-active:hidden" d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                <path class="hs-password-active:hidden" d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                <path class="hs-password-active:hidden" d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                <line class="hs-password-active:hidden" x1="2" x2="22" y1="2" y2="22"/>
                <path class="hidden hs-password-active:block" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                <circle class="hidden hs-password-active:block" cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-white">
            Confirmar contraseña
          </label>
          <input
            v-model="passwordConfirmation"
            type="password"
            :disabled="processing"
            placeholder="••••••••"
            class="py-2 sm:py-2.5 px-3 block w-full border border-slate-200 rounded-lg sm:text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 dark:bg-transparent dark:border-slate-700 dark:text-slate-300 dark:placeholder:text-white/60"
          />
        </div>

        <p v-if="error" class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>

        <button
          type="submit"
          :disabled="processing"
          class="py-2.5 px-3 w-full inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
        >
          <span v-if="processing" class="animate-spin inline-block size-4 border-[2px] border-t-transparent border-white rounded-full" />
          <span v-else>Restablecer contraseña</span>
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
    </template>

  </div>
</template>
