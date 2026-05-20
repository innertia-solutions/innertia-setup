<script setup>
definePageMeta({ layout: 'auth', middleware: ['auth'] })

const api = useApi()

const password = ref('')
const passwordConfirmation = ref('')
const processing = ref(false)
const error = ref('')

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
    await api.post('backoffice/auth/password/change', {
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    await navigateTo('/backoffice')
  } catch (e) {
    error.value = e?.data?.message ?? 'No pudimos actualizar la contraseña.'
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
        Cambiar contraseña
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Debes establecer una nueva contraseña para continuar.
      </p>
    </div>

    <form class="space-y-4" @submit.prevent="handleSubmit" novalidate>
      <div>
        <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-white">
          Nueva contraseña
        </label>
        <div class="relative">
          <input
            id="change-password"
            v-model="password"
            type="password"
            :disabled="processing"
            placeholder="••••••••"
            autocomplete="new-password"
            class="py-2 sm:py-2.5 px-3 block w-full border border-slate-200 rounded-lg sm:text-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 dark:bg-transparent dark:border-slate-700 dark:text-slate-300 dark:placeholder:text-white/60"
          />
          <button
            type="button"
            data-hs-toggle-password='{"target": "#change-password"}'
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
          autocomplete="new-password"
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
        <span v-else>Guardar contraseña</span>
      </button>
    </form>
  </div>
</template>
