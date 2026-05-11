<template>
  <div>
    <div class="mb-6 text-center">
      <h2 class="text-2xl font-bold text-gray-900">Iniciar sesión</h2>
      <p class="mt-1 text-sm text-gray-500">Ingresa tus credenciales para continuar</p>
    </div>

    <!-- Challenge notice -->
    <div v-if="challenge" class="mb-4 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
      Se requiere verificación adicional. Por favor, contacta al administrador del sistema.
    </div>

    <!-- Error message -->
    <div v-if="errorMsg" class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
      {{ errorMsg }}
    </div>

    <form class="space-y-5" @submit.prevent="handleSubmit">
      <VantageInput
        v-model="email"
        type="email"
        label="Correo electrónico"
        placeholder="tu@correo.com"
        :error="emailError"
      />

      <VantageInput
        v-model="password"
        type="password"
        label="Contraseña"
        placeholder="••••••••"
        :error="passwordError"
      />

      <VantageButton
        type="submit"
        severity="primary"
        class="w-full justify-center"
        :loading="loading"
      >
        Ingresar
      </VantageButton>
    </form>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'auth',
  middleware: ['guest'],
})

const { login } = useAuth()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMsg = ref('')
const emailError = ref('')
const passwordError = ref('')
const challenge = ref(false)

function validate(): boolean {
  emailError.value = ''
  passwordError.value = ''

  if (!email.value) {
    emailError.value = 'El correo es requerido.'
  } else if (!/\S+@\S+\.\S+/.test(email.value)) {
    emailError.value = 'El correo no es válido.'
  }

  if (!password.value) {
    passwordError.value = 'La contraseña es requerida.'
  }

  return !emailError.value && !passwordError.value
}

async function handleSubmit() {
  if (!validate()) return

  loading.value = true
  errorMsg.value = ''
  challenge.value = false

  const result = await login(email.value, password.value, 'backoffice')

  loading.value = false

  if (result.success) {
    await navigateTo('/backoffice')
  } else if (result.challenge) {
    challenge.value = true
  } else {
    errorMsg.value = result.error ?? 'Error al iniciar sesión.'
  }
}
</script>
