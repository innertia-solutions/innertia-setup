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
    // non-critical — tree shows empty
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
    await invalidateRoles()
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al actualizar el rol.')
  } finally {
    saving.value = false
  }
}

// All permission objects from groups, keyed by name
const allPermsMap = computed(() => {
  const map = {}
  for (const app of permGroups.value) {
    for (const g of app.groups ?? []) {
      for (const p of g.permissions ?? []) map[p.name] = p
    }
  }
  return map
})

const selectedPerms = computed(() =>
  form.permissions.map(name => allPermsMap.value[name] ?? { name, description: null })
)

const permSearch = ref('')
const filteredSelectedPerms = computed(() => {
  const q = permSearch.value.trim().toLowerCase()
  if (!q) return selectedPerms.value
  return selectedPerms.value.filter(p =>
    p.name.toLowerCase().includes(q) || (p.description ?? '').toLowerCase().includes(q)
  )
})

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

    <template v-else-if="role">
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

            <template v-else>
              <div class="relative">
                <svg class="absolute left-2 top-1/2 -translate-y-1/2 size-3 text-slate-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input
                  v-model="permSearch"
                  type="search"
                  placeholder="Filtrar…"
                  class="w-full pl-6 pr-2 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md text-slate-600 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500/30 focus:border-blue-400"
                />
              </div>

              <div v-if="filteredSelectedPerms.length === 0" class="py-3 text-center text-xs text-slate-400">
                Sin resultados.
              </div>

              <div v-else class="max-h-72 overflow-y-auto -mx-1">
                <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 px-1">
                  <div
                    v-for="p in filteredSelectedPerms"
                    :key="p.name"
                    class="flex items-center gap-1.5 rounded px-1.5 py-1 hover:bg-slate-50 dark:hover:bg-slate-800/40 min-w-0"
                    :title="p.description ?? p.name"
                  >
                    <span class="size-1.5 rounded-full bg-blue-500 shrink-0" />
                    <span class="text-xs font-mono text-slate-700 dark:text-slate-300 truncate">{{ p.name }}</span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div class="flex justify-end">
            <App.Button text="Guardar cambios" severity="primary" size="md" :loading="saving" @click="handleSave" />
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
            :apps="permGroups"
          />
        </div>

      </div>
    </template>

    <App.EmptyState v-else title="Rol no encontrado" description="El rol solicitado no existe o no tienes permisos para verlo." />
  </Admin.Page>
</template>
