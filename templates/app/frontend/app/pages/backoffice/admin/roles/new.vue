<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const loadingPermissions = ref(false)
const permissionGroups = ref([])

const form = reactive({
  name: '',
  description: '',
  permission_names: [],
})

const errors = reactive({
  name: '',
  description: '',
})

async function fetchPermissions() {
  loadingPermissions.value = true
  try {
    const groups = await api.get('backoffice/permissions')
    permissionGroups.value = groups?.data ?? groups ?? []
  } catch {
    toast.error('Error al cargar los permisos.')
  } finally {
    loadingPermissions.value = false
  }
}

function togglePermission(permName) {
  form.permission_names = form.permission_names.includes(permName)
    ? form.permission_names.filter(p => p !== permName)
    : [...form.permission_names, permName]
}

function toggleGroup(perms) {
  const names = perms.map(p => p.name)
  const allSelected = names.every(name => form.permission_names.includes(name))
  form.permission_names = allSelected
    ? form.permission_names.filter(name => !names.includes(name))
    : [...new Set([...form.permission_names, ...names])]
}

function isGroupSelected(perms) {
  return perms.every(p => form.permission_names.includes(p.name))
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
        permissions: form.permission_names,
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
        <AppButton text="Cancelar" severity="secondary" size="sm" type="link" link="/backoffice/admin/roles" />
      </template>
    </AdminPageHeader>

    <div class="max-w-2xl space-y-6">
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

        <AppEmptyState v-else-if="!permissionGroups.length" title="Sin permisos" description="No hay permisos disponibles." />

        <div v-else class="space-y-4">
          <div
            v-for="group in permissionGroups"
            :key="group.category"
            class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden"
          >
            <!-- Cabecera grupo -->
            <label class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/50 cursor-pointer">
              <input
                type="checkbox"
                class="rounded border-slate-300 dark:border-slate-600 text-blue-600 dark:text-blue-400"
                :checked="isGroupSelected(group.permissions)"
                @change="toggleGroup(group.permissions)"
              />
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex-1">{{ group.category_alias }}</span>
              <span class="text-xs text-slate-400">
                {{ group.permissions.filter(p => form.permission_names.includes(p.name)).length }}/{{ group.permissions.length }}
              </span>
            </label>

            <!-- Permisos individuales -->
            <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
              <label
                v-for="perm in group.permissions"
                :key="perm.id"
                class="flex items-start gap-2.5 cursor-pointer"
              >
                <input
                  type="checkbox"
                  class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-blue-600 dark:text-blue-400"
                  :checked="form.permission_names.includes(perm.name)"
                  @change="togglePermission(perm.name)"
                />
                <div class="min-w-0">
                  <span class="text-sm text-slate-800 dark:text-slate-200 block">{{ perm.name }}</span>
                  <span v-if="perm.description" class="text-xs text-slate-400 block truncate">{{ perm.description }}</span>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <AppButton text="Crear rol" severity="primary" size="md" :loading="loading" @click="handleSubmit" />
      </div>
    </div>
  </div>
</template>
