<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const loadingRoles = ref(false)
const roles = ref([])
const manualPassword = ref(false)

const form = reactive({
  name: '',
  email: '',
  role_id: '',
  password: '',
})

const errors = reactive({
  name: '',
  email: '',
  role_id: '',
  password: '',
})

async function fetchRoles() {
  loadingRoles.value = true
  try {
    const data = await api.get('backoffice/roles')
    roles.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los roles.')
  } finally {
    loadingRoles.value = false
  }
}

function generatePassword() {
  const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%'
  form.password = Array.from({ length: 12 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
}

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

async function handleSubmit() {
  clearErrors()
  loading.value = true
  try {
    await api.post('backoffice/users', {
      body: {
        name: form.name,
        email: form.email,
        role_id: form.role_id || undefined,
        password: form.password || undefined,
      }
    })
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

const roleOptions = computed(() =>
  roles.value.map(r => ({ value: r.id, label: r.name }))
)

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
          @click="navigateTo('/backoffice/admin/users')"
        />
      </template>
    </AdminPageHeader>

    <div class="max-w-xl">
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
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

        <FormsSelect
          v-model="form.role_id"
          label="Rol"
          placeholder="Seleccionar rol..."
          :options="roleOptions"
          :loading="loadingRoles"
          :error="errors.role_id || null"
        />

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Contraseña</label>
            <button
              type="button"
              class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
              @click="manualPassword = !manualPassword"
            >
              {{ manualPassword ? 'Auto-generar' : 'Ingresar manualmente' }}
            </button>
          </div>

          <div v-if="manualPassword">
            <FormsInput
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              :error="errors.password || null"
            />
          </div>
          <div v-else class="flex items-center gap-2">
            <input
              :value="form.password"
              readonly
              class="flex-1 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 px-3 py-2 text-sm font-mono text-slate-600 dark:text-slate-300"
              placeholder="(se generará automáticamente)"
            />
            <AppButton
              text="Generar"
              severity="secondary"
              size="sm"
              @click="generatePassword"
            />
          </div>
          <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
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
