<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const loadingPermissions = ref(false)
const permissions = ref([])

const form = reactive({
  name: '',
  description: '',
  permission_ids: [],
})

const errors = reactive({
  name: '',
  description: '',
})

async function fetchPermissions() {
  loadingPermissions.value = true
  try {
    const data = await api.get('backoffice/permissions')
    permissions.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los permisos.')
  } finally {
    loadingPermissions.value = false
  }
}

const groupedPermissions = computed(() => {
  const groups = {}
  for (const perm of permissions.value) {
    const category = perm.category ?? perm.name?.split('.')[0] ?? 'general'
    if (!groups[category]) groups[category] = []
    groups[category].push(perm)
  }
  return groups
})

function togglePermission(id) {
  if (form.permission_ids.includes(id)) {
    form.permission_ids = form.permission_ids.filter(p => p !== id)
  } else {
    form.permission_ids = [...form.permission_ids, id]
  }
}

function toggleGroup(perms) {
  const ids = perms.map(p => p.id)
  const allSelected = ids.every(id => form.permission_ids.includes(id))
  if (allSelected) {
    form.permission_ids = form.permission_ids.filter(id => !ids.includes(id))
  } else {
    form.permission_ids = [...new Set([...form.permission_ids, ...ids])]
  }
}

function isGroupSelected(perms) {
  return perms.every(p => form.permission_ids.includes(p.id))
}

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

async function handleSubmit() {
  clearErrors()
  loading.value = true
  try {
    await api.post('backoffice/roles', {
      body: {
        name: form.name,
        description: form.description || undefined,
        permission_ids: form.permission_ids,
      }
    })
    toast.success('Rol creado correctamente.')
    await navigateTo('/backoffice/admin/roles')
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al crear el rol.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchPermissions)
</script>

<template>
  <div>
    <AdminPageHeader title="Nuevo rol" description="Crea un nuevo rol y asigna sus permisos.">
      <template #actions>
        <AppButton
          text="Cancelar"
          severity="secondary"
          size="sm"
          @click="navigateTo('/backoffice/admin/roles')"
        />
      </template>
    </AdminPageHeader>

    <div class="max-w-2xl space-y-6">
      <!-- Basic info -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Información del rol</h2>

        <FormsInput
          v-model="form.name"
          label="Nombre del rol"
          placeholder="Ej. Supervisor"
          :error="errors.name || null"
        />

        <FormsInput
          v-model="form.description"
          label="Descripción"
          placeholder="Descripción breve del rol..."
          :error="errors.description || null"
        />
      </div>

      <!-- Permissions -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
        <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">Permisos</h2>

        <div v-if="loadingPermissions" class="py-8 flex items-center justify-center">
          <AppLoadingState label="Cargando permisos..." />
        </div>

        <div v-else-if="!permissions.length" class="py-6">
          <AppEmptyState title="Sin permisos" description="No hay permisos disponibles." />
        </div>

        <div v-else class="space-y-5">
          <div
            v-for="(perms, category) in groupedPermissions"
            :key="category"
            class="border border-slate-100 dark:border-slate-700 rounded-lg overflow-hidden"
          >
            <div class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50">
              <input
                :id="`group-${category}`"
                type="checkbox"
                class="rounded border-slate-300 dark:border-slate-600 text-blue-600"
                :checked="isGroupSelected(perms)"
                @change="toggleGroup(perms)"
              />
              <label :for="`group-${category}`" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 cursor-pointer">
                {{ category }}
              </label>
              <span class="ml-auto text-xs text-slate-400">{{ perms.filter(p => form.permission_ids.includes(p.id)).length }}/{{ perms.length }}</span>
            </div>
            <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
              <label
                v-for="perm in perms"
                :key="perm.id"
                class="flex items-center gap-2.5 cursor-pointer group"
              >
                <input
                  type="checkbox"
                  class="rounded border-slate-300 dark:border-slate-600 text-blue-600"
                  :checked="form.permission_ids.includes(perm.id)"
                  @change="togglePermission(perm.id)"
                />
                <span class="text-sm text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white">
                  {{ perm.name }}
                </span>
                <span v-if="perm.description" class="text-xs text-slate-400 truncate">{{ perm.description }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <AppButton
          text="Crear rol"
          severity="primary"
          size="md"
          :loading="loading"
          @click="handleSubmit"
        />
      </div>
    </div>
  </div>
</template>
