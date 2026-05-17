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
    // non-critical — tree shows empty
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

// All permission objects from groups, keyed by name
const allPermsMap = computed(() => {
  const map = {}
  for (const g of permGroups.value) {
    for (const p of g.permissions ?? []) map[p.name] = p
  }
  return map
})

const selectedPerms = computed(() =>
  form.permissions.map(name => allPermsMap.value[name] ?? { name, description: null })
)

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

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 items-start">

      <!-- Left column: info + selected permissions -->
      <div class="lg:col-span-2 space-y-5">

        <div class="bg-card border border-card-line rounded-2xl p-6 space-y-5">
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Información del rol</p>
          <Forms.Input v-model="form.name" label="Nombre" placeholder="Ej. Supervisor" :error="errors.name || null" />
          <Forms.Input v-model="form.description" label="Descripción" placeholder="Descripción breve del rol…" :error="errors.description || null" />
        </div>

        <div class="bg-card border border-card-line rounded-2xl p-6 space-y-3">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Permisos asignados</p>
            <span class="text-xs tabular-nums text-slate-400 dark:text-slate-500">{{ form.permissions.length }}</span>
          </div>

          <div v-if="selectedPerms.length === 0" class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
            Sin permisos seleccionados.
          </div>

          <ul v-else class="space-y-1 max-h-96 overflow-y-auto pr-1">
            <li
              v-for="p in selectedPerms"
              :key="p.name"
              class="flex items-start gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800/40 group"
            >
              <span class="mt-0.5 size-1.5 rounded-full bg-blue-500 shrink-0" />
              <div class="min-w-0">
                <p class="text-xs font-mono text-slate-700 dark:text-slate-300 truncate">{{ p.name }}</p>
                <p v-if="p.description" class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ p.description }}</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="flex justify-end">
          <App.Button text="Crear rol" severity="primary" size="md" :loading="loading" @click="handleSubmit" />
        </div>
      </div>

      <!-- Right column: permissions tree -->
      <div class="lg:col-span-3 bg-card border border-card-line rounded-2xl p-6 space-y-4">
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

    </div>
  </Admin.Page>
</template>
