<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const roles = ref([])

async function fetchRoles() {
  loading.value = true
  try {
    const data = await api.get('backoffice/roles')
    roles.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los roles.')
  } finally {
    loading.value = false
  }
}

async function deleteRole(id) {
  if (!confirm('¿Seguro que deseas eliminar este rol?')) return
  try {
    await api.delete(`backoffice/roles/${id}`)
    toast.success('Rol eliminado.')
    await fetchRoles()
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al eliminar el rol.')
  }
}

onMounted(fetchRoles)
</script>

<template>
  <div>
    <AdminPageHeader title="Roles" description="Gestión de roles y sus permisos.">
      <template #actions>
        <AppButton
          text="Nuevo rol"
          severity="primary"
          size="sm"
          @click="navigateTo('/backoffice/admin/roles/new')"
        />
      </template>
    </AdminPageHeader>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState label="Cargando roles..." />
      </div>

      <div v-else-if="!roles.length" class="px-5 py-16">
        <AppEmptyState
          title="Sin roles"
          description="Aún no hay roles creados en el sistema."
        />
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Descripción</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Permisos</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuarios</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="role in roles"
              :key="role.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
            >
              <td class="px-5 py-3 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ role.name }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ role.description ?? '—' }}</td>
              <td class="px-5 py-3 text-sm text-slate-700 dark:text-slate-300">
                {{ role.permissions_count ?? role.permissions?.length ?? '—' }}
              </td>
              <td class="px-5 py-3 text-sm text-slate-700 dark:text-slate-300">
                {{ role.users_count ?? role.users?.length ?? '—' }}
              </td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <AppButton
                    text="Editar"
                    severity="secondary"
                    size="xs"
                    variant="dropdown"
                    @click="navigateTo(`/backoffice/admin/roles/${role.id}`)"
                  />
                  <AppButton
                    text="Eliminar"
                    severity="danger"
                    size="xs"
                    variant="dropdown"
                    @click="deleteRole(role.id)"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
