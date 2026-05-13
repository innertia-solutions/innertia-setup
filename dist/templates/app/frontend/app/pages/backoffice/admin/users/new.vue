<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const toast = useToast()
const { store } = useUsers()
const { index: fetchRolesApi } = useRoles()

const loading = ref(false)
const loadingRoles = ref(false)
const roles = ref([])
const selectedRoles = ref([])

// Activation mode: 'email' | 'manual'
const activationMode = ref('email')

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

function toggleRole(roleName) {
  selectedRoles.value = selectedRoles.value.includes(roleName)
    ? selectedRoles.value.filter(r => r !== roleName)
    : [...selectedRoles.value, roleName]
}

async function fetchRoles() {
  loadingRoles.value = true
  try {
    const data = await fetchRolesApi()
    roles.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los roles.')
  } finally {
    loadingRoles.value = false
  }
}

async function handleSubmit() {
  clearErrors()
  loading.value = true
  try {
    const payload = {
      name: form.name,
      email: form.email,
      send_activation: activationMode.value === 'email',
      roles: selectedRoles.value,
    }
    if (activationMode.value === 'manual') {
      payload.password = form.password
      payload.password_confirmation = form.password_confirmation
    }
    await store(payload)
    toast.success('Usuario creado correctamente.')
    await navigateTo('/backoffice/admin/users')
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al crear el usuario.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchRoles)
</script>

<template>
  <div>
    <AdminPageHeader title="Nuevo usuario" description="Completa los campos para crear un usuario.">
      <template #actions>
        <AppButton
          text="Cancelar"
          severity="secondary"
          size="sm"
          type="link"
          link="/backoffice/admin/users"
        />
      </template>
    </AdminPageHeader>

    <div class="max-w-xl">
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-5">

        <FormsInput
          v-model="form.name"
          label="Nombre completo"
          placeholder="Ej. Ana García"
          :error="errors.name || null"
        />

        <FormsInput
          v-model="form.email"
          type="email"
          label="Correo electrónico"
          placeholder="usuario@empresa.com"
          :error="errors.email || null"
        />

        <!-- Activation mode toggle -->
        <div>
          <label class="block text-sm font-medium text-slate-800 dark:text-slate-200 mb-2">Acceso</label>
          <div class="flex gap-1 bg-slate-100 dark:bg-slate-700/50 rounded-lg p-1 w-fit mb-3">
            <button
              type="button"
              class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors"
              :class="activationMode === 'email'
                ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
              @click="activationMode = 'email'"
            >
              Enviar email de activación
            </button>
            <button
              type="button"
              class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors"
              :class="activationMode === 'manual'
                ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
              @click="activationMode = 'manual'"
            >
              Establecer contraseña manualmente
            </button>
          </div>

          <p v-if="activationMode === 'email'" class="text-xs text-slate-400 dark:text-slate-500">
            Se enviará un email al usuario para que establezca su contraseña.
          </p>

          <div v-else class="space-y-3">
            <FormsInput
              v-model="form.password"
              type="password"
              label="Contraseña"
              placeholder="••••••••"
              :error="errors.password || null"
            />
            <FormsInput
              v-model="form.password_confirmation"
              type="password"
              label="Confirmar contraseña"
              placeholder="••••••••"
              :error="errors.password_confirmation || null"
            />
          </div>
        </div>

        <!-- Roles -->
        <div>
          <label class="block text-sm font-medium text-slate-800 dark:text-slate-200 mb-2">Roles</label>
          <div v-if="loadingRoles" class="text-sm text-slate-500 dark:text-slate-400">Cargando roles...</div>
          <div v-else-if="!roles.length" class="text-sm text-slate-400">No hay roles disponibles.</div>
          <div v-else class="flex flex-wrap gap-2">
            <button
              v-for="role in roles"
              :key="role.id ?? role.name"
              type="button"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
              :class="selectedRoles.includes(role.name)
                ? 'bg-blue-600 border-blue-600 text-white'
                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-blue-500'"
              @click="toggleRole(role.name)"
            >
              {{ role.name }}
            </button>
          </div>
        </div>

        <div class="pt-2 flex justify-end">
          <AppButton
            text="Crear usuario"
            severity="primary"
            size="md"
            :loading="loading"
            @click="handleSubmit"
          />
        </div>
      </div>
    </div>
  </div>
</template>
