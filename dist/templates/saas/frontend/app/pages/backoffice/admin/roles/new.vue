<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api   = useApi()
const toast = useToast()

const router             = useRouter()
const loading            = ref(false)
const loadingPermissions = ref(false)
const permGroups         = ref([])

const form = reactive({
  name:        '',
  description: '',
  permissions: [],
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
    await invalidateRoles()
    ok = true
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al crear el rol.')
  } finally {
    loading.value = false
  }
  if (ok) router.push('/backoffice/admin/roles')
}

// Build a rich map: name → { name, description, app, app_label, category, category_alias }
const allPermsMap = computed(() => {
  const map = {}
  for (const app of permGroups.value) {
    for (const g of app.groups ?? []) {
      for (const p of g.permissions ?? []) {
        map[p.name] = {
          ...p,
          app:            app.app,
          app_label:      app.app_label,
          category:       g.category,
          category_alias: g.category_alias,
        }
      }
    }
  }
  return map
})

const selectedPerms = computed(() =>
  form.permissions.map(name => allPermsMap.value[name] ?? { name, description: null, app: null, app_label: null, category: null, category_alias: null })
)

const permSearch = ref('')
const filteredSelectedPerms = computed(() => {
  const q = permSearch.value.trim().toLowerCase()
  if (!q) return selectedPerms.value
  return selectedPerms.value.filter(p =>
    p.name.toLowerCase().includes(q) ||
    (p.description ?? '').toLowerCase().includes(q) ||
    (p.app_label ?? '').toLowerCase().includes(q) ||
    (p.category_alias ?? '').toLowerCase().includes(q)
  )
})

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

      <!-- Left column -->
      <div class="lg:col-span-2 space-y-5">

        <div class="bg-card border border-card-line rounded-2xl p-6 space-y-5">
          <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Información del rol</p>
          <Forms.Input v-model="form.name" label="Nombre" placeholder="Ej. Supervisor" :error="errors.name || null" />
          <Forms.Input v-model="form.description" label="Descripción" placeholder="Descripción breve del rol…" :error="errors.description || null" />
        </div>

        <!-- Assigned permissions -->
        <div class="bg-card border border-card-line rounded-2xl p-6 space-y-3">
          <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Permisos asignados</p>
            <span class="text-xs tabular-nums text-slate-400 dark:text-slate-500">{{ form.permissions.length }}</span>
          </div>

          <div v-if="selectedPerms.length === 0" class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
            Sin permisos seleccionados.
          </div>

          <template v-else>
            <!-- search -->
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

            <!-- 4-col table -->
            <div v-else class="max-h-72 overflow-y-auto -mx-3">
              <table class="w-full text-xs border-collapse">
                <thead class="sticky top-0 z-10">
                  <tr class="bg-slate-50 dark:bg-slate-800 text-left">
                    <th class="px-3 py-1.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-[10px] border-b border-slate-200 dark:border-slate-700">App</th>
                    <th class="px-3 py-1.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-[10px] border-b border-slate-200 dark:border-slate-700">Grupo</th>
                    <th class="px-3 py-1.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-[10px] border-b border-slate-200 dark:border-slate-700">Permiso</th>
                    <th class="px-3 py-1.5 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide text-[10px] border-b border-slate-200 dark:border-slate-700">Descripción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="p in filteredSelectedPerms"
                    :key="p.name"
                    class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                  >
                    <td class="px-3 py-1.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ p.app_label ?? p.app ?? '—' }}</td>
                    <td class="px-3 py-1.5 text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ p.category_alias ?? p.category ?? '—' }}</td>
                    <td class="px-3 py-1.5 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ p.name }}</td>
                    <td class="px-3 py-1.5 text-slate-400 dark:text-slate-500 truncate max-w-[180px]">{{ p.description ?? '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>
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
          :apps="permGroups"
        />
      </div>

    </div>
  </Admin.Page>
</template>
