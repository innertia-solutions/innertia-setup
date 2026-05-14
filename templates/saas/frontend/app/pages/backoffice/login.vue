<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const { performLogin } = useAuth()
const config = useRuntimeConfig()

const form = useForm({
  email:    { value: '', rules: ['required', 'email'] },
  password: { value: '', rules: ['required', { name: 'min', arg: 8 }] },
})

const processing = ref(false)

async function handleSubmit() {
  if (!form.validate()) return

  processing.value = true
  try {
    const data = await performLogin('backoffice', form.values.email, form.values.password)
    if (data?.requires_password_change) {
      await navigateTo('/backoffice/auth/change-password')
    } else {
      await navigateTo(config.public.homePath || '/backoffice')
    }
  } catch (e) {
    form.addError('password', e?.data?.message ?? 'Credenciales incorrectas.')
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
        Bienvenido
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-500">
        Ingresa tus credenciales para continuar.
      </p>
    </div>

    <form class="space-y-4" @submit.prevent="handleSubmit" novalidate>

      <div>
        <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-white">
          Correo electrónico
        </label>
        <input
          v-model="form.values.email"
          type="email"
          :disabled="processing"
          autocomplete="email"
          placeholder="tu@correo.com"
          @blur="form.validate('email')"
          class="py-2 sm:py-2.5 px-3 block w-full border rounded-lg sm:text-sm placeholder:text-slate-400 focus:ring-1 disabled:opacity-50 disabled:pointer-events-none dark:bg-transparent dark:text-slate-300 dark:placeholder:text-white/60"
          :class="form.errors.email?.length
            ? 'border-red-400 focus:border-red-400 focus:ring-red-400'
            : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:focus:ring-slate-600'"
        />
        <p v-if="form.errors.email?.length" class="mt-1.5 text-xs text-red-500">
          {{ form.errors.email[0] }}
        </p>
      </div>

      <div>
        <div class="flex justify-between items-center mb-2">
          <label class="text-sm font-medium text-slate-800 dark:text-white">
            Contraseña
          </label>
          <NuxtLink
            to="/backoffice/auth/forgot-password"
            class="text-xs text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 hover:underline"
          >
            Recuperar contraseña
          </NuxtLink>
        </div>
        <div class="relative">
          <input
            id="password"
            v-model="form.values.password"
            type="password"
            :disabled="processing"
            autocomplete="current-password"
            placeholder="••••••••"
            @blur="form.validate('password')"
            class="py-2 sm:py-2.5 px-3 block w-full border rounded-lg sm:text-sm placeholder:text-slate-400 focus:ring-1 disabled:opacity-50 disabled:pointer-events-none dark:bg-transparent dark:text-slate-300 dark:placeholder:text-white/60"
            :class="form.errors.password?.length
              ? 'border-red-400 focus:border-red-400 focus:ring-red-400'
              : 'border-slate-200 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:focus:ring-slate-600'"
          />
          <button
            type="button"
            data-hs-toggle-password='{"target": "#password"}'
            class="absolute inset-y-0 end-0 flex items-center z-20 px-3 cursor-pointer text-slate-400 rounded-e-md focus:outline-hidden focus:text-blue-800 dark:text-slate-600 dark:focus:text-blue-500"
          >
            <svg class="shrink-0 size-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path class="hs-password-active:hidden" d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
              <path class="hs-password-active:hidden" d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
              <path class="hs-password-active:hidden" d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
              <line class="hs-password-active:hidden" x1="2" x2="22" y1="2" y2="22"/>
              <path class="hidden hs-password-active:block" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
              <circle class="hidden hs-password-active:block" cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
        <p v-if="form.errors.password?.length" class="mt-1.5 text-xs text-red-500">
          {{ form.errors.password[0] }}
        </p>
      </div>

      <button
        type="submit"
        :disabled="processing"
        class="py-2.5 px-3 w-full inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none"
      >
        <span v-if="processing" class="animate-spin inline-block size-4 border-[2px] border-t-transparent border-white rounded-full" />
        <span v-else class="flex items-center gap-x-2">
          Ingresar
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </span>
      </button>

    </form>

    <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
      <ul class="flex flex-wrap justify-center items-center gap-3">
        <li class="inline-flex items-center relative text-xs text-slate-500 pe-3.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:size-[3px] after:bg-slate-400 after:rounded-full after:-translate-y-1/2 dark:text-slate-400">
          © {{ new Date().getFullYear() }} pomely.
        </li>
        <li class="inline-flex items-center relative text-xs text-slate-500 pe-3.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:size-[3px] after:bg-slate-400 after:rounded-full after:-translate-y-1/2 dark:text-slate-400">
          <NuxtLink to="/terms" class="text-xs text-slate-500 underline-offset-4 hover:underline hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
            Términos y condiciones
          </NuxtLink>
        </li>
        <li class="inline-flex items-center relative text-xs text-slate-500 pe-3.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:size-[3px] after:bg-slate-400 after:rounded-full after:-translate-y-1/2 dark:text-slate-400">
          <NuxtLink to="/privacy" class="text-xs text-slate-500 underline-offset-4 hover:underline hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
            Política de privacidad
          </NuxtLink>
        </li>
      </ul>
    </div>
  </div>
</template>
