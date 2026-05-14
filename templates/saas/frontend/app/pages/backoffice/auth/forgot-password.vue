<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const api = useApi()

const form = useForm({
  email: { value: '', rules: ['required', 'email'] },
})

const processing = ref(false)
const sent = ref(false)

async function handleSubmit() {
  if (!form.validate()) return

  processing.value = true
  try {
    await api.post('backoffice/auth/password/forgot', { email: form.values.email })
    sent.value = true
  } catch (e) {
    form.addError('email', e?.data?.message ?? 'No pudimos procesar la solicitud.')
  } finally {
    processing.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-semibold text-slate-800 dark:text-slate-200">
        Recuperar contraseña
      </h1>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        Ingresa tu correo y te enviaremos las instrucciones.
      </p>
    </div>

    <!-- Éxito -->
    <div
      v-if="sent"
      class="p-4 bg-green-50 dark:bg-green-500/10 rounded-xl flex items-start gap-3"
    >
      <svg class="size-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      <p class="text-sm text-green-700 dark:text-green-300">
        Si existe una cuenta con ese correo, recibirás un enlace para restablecer tu contraseña.
      </p>
    </div>

    <!-- Formulario -->
    <form v-else class="space-y-4" @submit.prevent="handleSubmit" novalidate>
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

      <button
        type="submit"
        :disabled="processing"
        class="py-2.5 px-3 w-full inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none"
      >
        <span v-if="processing" class="animate-spin inline-block size-4 border-[2px] border-t-transparent border-white rounded-full" />
        <span v-else class="flex items-center gap-x-2">
          Enviar instrucciones
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </span>
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
