<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const route = useRoute()
const api = useApi()
const toast = useToast()

const id = computed(() => route.params.id)
const loading = ref(false)
const loadingPermissions = ref(false)
const saving = ref(false)

const role = ref(null)
const permissions = ref([])

const form = reactive({
  name: '',
  description: '',
  permission_ids: [],
})

const errors = reactive({ name: '', description: '' })

async function fetchRole() {
  loading.value = true
  try {
    const data = await api.get(`backoffice/roles/${id.value}`)
    role.value = data?.data ?? data
    form.name = role.value.name ?? ''
    form.description = role.value.description ?? ''
    form.permission_ids = (role.value.permissions ?? []).map(p => p.id ?? p)
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

function togglePermission(permId) {
  form.permission_ids = form.permission_ids.includes(permId)
    ? form.permission_ids.filter(p => p !== permId)
    : [...form.permission_ids, permId]
}

function toggleGroup(perms) {
  const ids = perms.map(p => p.id)
  const allSelected = ids.every(id => form.permission_ids.includes(id))
  form.permission_ids = allSelected
    ? form.permission_ids.filter(id => !ids.includes(id))
    : [...new Set([...form.permission_ids, ...ids])]
}

function isGroupSelected(perms) {
  return perms.every(p => form.permission_ids.includes(p.id))
}

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

async function handleSave() {
  clearErrors()
  saving.value = true
  try {
    await api.put(`backoffice/roles/${id.value}`, {
      body: {
        name: form.name,
        description: form.description || undefined,
        permission_ids: form.permission_ids,
      }
    })
    toast.success('Rol actualizado correctamente.')
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
  <div>
    <AdminPageHeader title="Editar rol" description="Modifica el nombre, descripción y permisos del rol.">
      <template #actions>
        <AppButton text="Volver" severity="secondary" size="sm" type="link" link="/backoffice/admin/roles" />
      </template>
    </AdminPageHeader>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <AppLoadingState />
    </div>

    <div v-else-if="role" class="max-w-2xl space-y-6">
      <!-- Información básica -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-5">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Información del rol</h2>
        <FormsInput v-model="form.name" label="Nombre del rol" placeholder="Ej. Supervisor" :error="errors.name || null" />
        <FormsInput v-model="form.description" label="Descripción" placeholder="Descripción breve del rol..." :error="errors.description || null" />
      </div>

      <!-- Permisos -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">Permisos</h2>

        <div v-if="loadingPermissions" class="py-8 flex items-center justify-center">
          <AppLoadingState />
        </div>

        <AppEmptyState v-else-if="!permissions.length" title="Sin permisos" description="No hay permisos disponibles." />

        <div v-else class="space-y-4">
          <div
            v-for="(perms, category) in groupedPermissions"
            :key="category"
            class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden"
          >
            <label class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 cursor-pointer">
              <input
                type="checkbox"
                class="rounded border-slate-300 dark:border-slate-600 text-blue-600 dark:text-blue-400"
                :checked="isGroupSelected(perms)"
                @change="toggleGroup(perms)"
              />
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex-1">{{ category }}</span>
              <span class="text-xs text-slate-400">
                {{ perms.filter(p => form.permission_ids.includes(p.id)).length }}/{{ perms.length }}
              </span>
            </label>

            <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
              <label
                v-for="perm in perms"
                :key="perm.id"
                class="flex items-center gap-2.5 cursor-pointer"
              >
                <input
                  type="checkbox"
                  class="rounded border-slate-300 dark:border-slate-600 text-blue-600 dark:text-blue-400"
                  :checked="form.permission_ids.includes(perm.id)"
                  @change="togglePermission(perm.id)"
                />
                <span class="text-sm text-slate-800 dark:text-slate-200">{{ perm.name }}</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <AppButton text="Guardar cambios" severity="primary" size="md" :loading="saving" @click="handleSave" />
      </div>
    </div>

    <div v-else class="py-16">
      <AppEmptyState title="Rol no encontrado" description="El rol solicitado no existe o no tienes permisos para verlo." />
    </div>
  </div>
</template>
