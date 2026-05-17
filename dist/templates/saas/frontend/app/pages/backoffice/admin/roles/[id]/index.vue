<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const route  = useRoute()
const api    = useApi()
const toast  = useToast()

const id     = computed(() => route.params.id)
const loading            = ref(false)
const loadingPermissions = ref(false)
const saving             = ref(false)

const role        = ref(null)
const permGroups  = ref([])

const form = reactive({
  name:        '',
  description: '',
  permissions: [],   // array of permission names
})

const errors = reactive({ name: '', description: '' })

async function fetchRole() {
  loading.value = true
  try {
    const data = await api.get(`backoffice/roles/${id.value}`)
    role.value = data?.data ?? data
    form.name        = role.value.name ?? ''
    form.description = role.value.description ?? ''
    form.permissions = (role.value.permissions ?? []).map(p => p.name ?? p)
  } catch {
    toast.error('Error al cargar el rol.')
  } finally {
    loading.value = false
  }
}

async function fetchPermissions() {
  loadingPermissions.value = true
  try {
    const data = await api.get('backoffice/permissions')
    permGroups.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los permisos.')
  } finally {
    loadingPermissions.value = false
  }
}

const { invalidate: invalidateRoles } = useTable(Tables.roles)

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

async function handleSave() {
  clearErrors()
  saving.value = true
  try {
    await api.put(`backoffice/roles/${id.value}`, { name: form.name, description: form.description || undefined })
    await api.post(`backoffice/roles/${id.value}/permissions`, { permissions: form.permissions })
    toast.success('Rol actualizado correctamente.')
    invalidateRoles().catch(() => {})
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al actualizar el rol.')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchRole()
  fetchPermissions()
})
</script>

<template>
  <Admin.Page
    title="Editar rol"
    description="Modifica el nombre, descripción y permisos del rol."
    icon="IconShieldCheck"
    color="gray"
  >
    <template #breadcrumb>
      <NuxtLink to="/backoffice" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Inicio</NuxtLink>
      <span class="text-xs text-slate-300 dark:text-slate-600">/</span>
      <NuxtLink to="/backoffice/admin/roles" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Roles</NuxtLink>
    </template>
    <template #actions>
      <App.Button type="link" link="/backoffice/admin/roles" text="Volver" severity="secondary" size="sm" />
    </template>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <App.LoadingState />
    </div>

    <div v-else-if="role" class="max-w-3xl space-y-5">

      <div class="bg-card border border-card-line rounded-2xl p-6 space-y-5">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Información del rol</p>
        <Forms.Input v-model="form.name" label="Nombre" placeholder="Ej. Supervisor" :error="errors.name || null" />
        <Forms.Input v-model="form.description" label="Descripción" placeholder="Descripción breve del rol…" :error="errors.description || null" />
      </div>

      <div class="bg-card border border-card-line rounded-2xl p-6 space-y-4">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Permisos</p>
        <div v-if="loadingPermissions" class="py-10 flex items-center justify-center">
          <App.LoadingState />
        </div>
        <PermissionsTree
          v-else
          v-model="form.permissions"
          :groups="permGroups"
        />
      </div>

      <div class="flex justify-end">
        <App.Button text="Guardar cambios" severity="primary" size="md" :loading="saving" @click="handleSave" />
      </div>
    </div>

    <App.EmptyState v-else title="Rol no encontrado" description="El rol solicitado no existe o no tienes permisos para verlo." />
  </Admin.Page>
</template>
