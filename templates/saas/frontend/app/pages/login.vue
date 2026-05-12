<script setup>
definePageMeta({ layout: 'auth', middleware: ['guest'] })

const { performLogin } = useAuth()
const config = useRuntimeConfig()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleSubmit() {
  loading.value = true
  error.value = ''
  try {
    const data = await performLogin('backoffice', email.value, password.value)
    if (data?.requires_password_change) {
      await navigateTo('/backoffice/change-password')
    } else {
      await navigateTo(config.public.homePath || '/backoffice')
    }
  } catch (e) {
    error.value = e?.data?.message ?? 'Credenciales incorrectas.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div>
    <h1 class="text-xl font-semibold text-slate-900 dark:text-white">Iniciar sesión</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ingresa tus credenciales para continuar.</p>

    <form class="mt-6 space-y-4" @submit.prevent="handleSubmit">
      <FormsInput
        v-model="email"
        type="email"
        label="Correo electrónico"
        placeholder="tu@correo.com"
        autocomplete="email"
      />

      <FormsInput
        v-model="password"
        type="password"
        label="Contraseña"
        placeholder="••••••••"
        autocomplete="current-password"
        :error="error || null"
      />

      <AppButton
        text="Ingresar"
        severity="primary"
        size="md"
        :loading="loading"
        class="w-full"
        @click="handleSubmit"
      />
    </form>
  </div>
</template>
