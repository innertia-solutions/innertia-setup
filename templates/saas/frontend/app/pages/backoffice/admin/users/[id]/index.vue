<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const route = useRoute()
const api = useApi()
const toast = useToast()

const id = computed(() => route.params.id)
const loading = ref(false)
const loadingRoles = ref(false)
const saving = ref(false)

const user = ref(null)
const roles = ref([])

const form = reactive({
  name: '',
  email: '',
  role_ids: [],
})

const errors = reactive({
  name: '',
  email: '',
  role_ids: '',
})

async function fetchUser() {
  loading.value = true
  try {
    const data = await api.get(`backoffice/users/${id.value}`)
    user.value = data?.data ?? data
    form.name = user.value.name ?? ''
    form.email = user.value.email ?? ''
    form.role_ids = (user.value.roles ?? []).map(r => r.id ?? r)
  } catch {
    toast.error('Error al cargar el usuario.')
  } finally {
    loading.value = false
  }
}

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

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

function toggleRole(roleId) {
  form.role_ids = form.role_ids.includes(roleId)
    ? form.role_ids.filter(r => r !== roleId)
    : [...form.role_ids, roleId]
}

async function handleSave() {
  clearErrors()
  saving.value = true
  try {
    await api.put(`backoffice/users/${id.value}`, {
      body: { name: form.name, email: form.email, role_ids: form.role_ids }
    })
    toast.success('Usuario actualizado correctamente.')
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al actualizar el usuario.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchUser()
  fetchRoles()
})
</script>

<template>
  <div>
    <AdminPageHeader title="Detalle de usuario" description="Ver y editar información del usuario.">
      <template #actions>
        <AppButton
          text="Volver"
          severity="secondary"
          size="sm"
          type="link"
          link="/backoffice/admin/users"
        />
      </template>
    </AdminPageHeader>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingState />
    </div>

    <div v-else-if="user" class="max-w-xl space-y-6">
      <!-- Formulario -->
      <div class="bg-card border border-card-line rounded-xl p-6 space-y-5">
        <h2 class="text-sm font-semibold text-foreground">Información general</h2>

        <FormsInput
          v-model="form.name"
          label="Nombre completo"
          placeholder="Nombre del usuario"
          :error="errors.name || null"
        />

        <FormsInput
          v-model="form.email"
          type="email"
          label="Correo electrónico"
          placeholder="usuario@empresa.com"
          :error="errors.email || null"
        />

        <!-- Roles toggle -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-2">Roles</label>
          <div v-if="loadingRoles" class="text-sm text-muted-foreground">Cargando roles...</div>
          <div v-else class="flex flex-wrap gap-2">
            <button
              v-for="role in roles"
              :key="role.id"
              type="button"
              class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
              :class="form.role_ids.includes(role.id)
                ? 'bg-primary border-primary text-primary-foreground'
                : 'bg-card border-card-line text-muted-foreground hover:border-primary'"
              @click="toggleRole(role.id)"
            >
              {{ role.name }}
            </button>
          </div>
          <p v-if="errors.role_ids" class="mt-1 text-xs text-red-500">{{ errors.role_ids }}</p>
        </div>

        <div class="pt-2 flex justify-end">
          <AppButton
            text="Guardar cambios"
            severity="primary"
            size="md"
            :loading="saving"
            @click="handleSave"
          />
        </div>
      </div>

      <!-- Meta info -->
      <div class="bg-card border border-card-line rounded-xl p-6">
        <h2 class="text-sm font-semibold text-foreground mb-4">Información del sistema</h2>
        <dl class="space-y-3">
          <div class="flex items-center justify-between">
            <dt class="text-sm text-muted-foreground">ID</dt>
            <dd class="text-sm font-mono text-foreground">{{ user.id }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-sm text-muted-foreground">Estado</dt>
            <dd>
              <AppTag
                :text="user.active !== false ? 'Activo' : 'Inactivo'"
                :severity="user.active !== false ? 'success' : 'secondary'"
                size="xs"
              />
            </dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-sm text-muted-foreground">Creado</dt>
            <dd class="text-sm text-foreground">{{ user.created_at ?? '—' }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt class="text-sm text-muted-foreground">Última sesión</dt>
            <dd class="text-sm text-foreground">{{ user.last_login_at ?? '—' }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <div v-else class="py-16">
      <AppEmptyState title="Usuario no encontrado" description="El usuario solicitado no existe o no tienes permisos para verlo." />
    </div>
  </div>
</template>
