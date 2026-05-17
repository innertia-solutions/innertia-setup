<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api   = useApi()
const toast = useToast()

const loading            = ref(false)
const loadingPermissions = ref(false)
const permGroups         = ref([])

const form = reactive({
  name:        '',
  description: '',
  permissions: [],   // array of permission names
})

const errors = reactive({ name: '', description: '' })

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

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

const { invalidate: invalidateRoles } = useTable(Tables.roles)

async function handleSubmit() {
  clearErrors()
  loading.value = true
  let ok = false
  try {
    const res = await api.post('backoffice/roles', { name: form.name, description: form.description || undefined })
    const roleId = (res?.data ?? res)?.id
    if (roleId && form.permissions.length) {
      await api.post(`backoffice/roles/${roleId}/permissions`, { permissions: form.permissions })
    }
    toast.success('Rol creado correctamente.')
    invalidateRoles().catch(() => {})
    ok = true
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al crear el rol.')
  } finally {
    loading.value = false
  }
  if (ok) navigateTo('/backoffice/admin/roles')
}

onMounted(fetchPermissions)
</script>

<template>
  <Admin.Page
    title="Nuevo rol"
    description="Crea un nuevo rol y asigna sus permisos."
    icon="IconShieldCheck"
    color="gray"
  >
    <template #breadcrumb>
      <NuxtLink to="/backoffice" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Inicio</NuxtLink>
      <span class="text-xs text-slate-300 dark:text-slate-600">/</span>
      <NuxtLink to="/backoffice/admin/roles" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Roles</NuxtLink>
    </template>
    <template #actions>
      <App.Button type="link" link="/backoffice/admin/roles" text="Cancelar" severity="secondary" size="sm" />
    </template>

    <div class="max-w-3xl space-y-5">

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
        <App.Button text="Crear rol" severity="primary" size="md" :loading="loading" @click="handleSubmit" />
      </div>
    </div>
  </Admin.Page>
</template>
