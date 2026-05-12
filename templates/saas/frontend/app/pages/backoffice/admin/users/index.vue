<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const users = ref([])

async function fetchUsers() {
  loading.value = true
  try {
    const data = await api.get('backoffice/users')
    users.value = data?.data ?? data ?? []
  } catch (e) {
    toast.error('Error al cargar los usuarios.')
  } finally {
    loading.value = false
  }
}

async function deleteUser(id) {
  if (!confirm('¿Seguro que deseas eliminar este usuario?')) return
  try {
    await api.delete(`backoffice/users/${id}`)
    toast.success('Usuario eliminado.')
    await fetchUsers()
  } catch (e) {
    toast.error('Error al eliminar el usuario.')
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div>
    <AdminPageHeader title="Usuarios" description="Gestión de usuarios del sistema.">
      <template #actions>
        <AppButton
          text="Nuevo usuario"
          severity="primary"
          size="sm"
          @click="navigateTo('/backoffice/admin/users/new')"
        />
      </template>
    </AdminPageHeader>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <!-- Loading -->
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState label="Cargando usuarios..." />
      </div>

      <!-- Empty -->
      <div v-else-if="!users.length" class="px-5 py-16">
        <AppEmptyState
          title="Sin usuarios"
          description="Aún no hay usuarios registrados en el sistema."
        />
      </div>

      <!-- Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Roles</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Creado</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="user in users"
              :key="user.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
            >
              <td class="px-5 py-3 text-sm font-medium text-slate-800 dark:text-slate-200">{{ user.name }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ user.email }}</td>
              <td class="px-5 py-3">
                <div class="flex flex-wrap gap-1">
                  <AppTag
                    v-for="role in (user.roles ?? [])"
                    :key="role.id ?? role"
                    :text="role.name ?? role"
                    severity="secondary"
                    size="xs"
                  />
                  <span v-if="!user.roles?.length" class="text-xs text-slate-400">—</span>
                </div>
              </td>
              <td class="px-5 py-3">
                <AppTag
                  :text="user.active !== false ? 'Activo' : 'Inactivo'"
                  :severity="user.active !== false ? 'success' : 'secondary'"
                  size="xs"
                />
              </td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ user.created_at ?? '—' }}</td>
              <td class="px-5 py-3">
                <div class="flex items-center gap-2">
                  <AppButton
                    text="Ver"
                    severity="secondary"
                    size="xs"
                    variant="dropdown"
                    @click="navigateTo(`/backoffice/admin/users/${user.id}`)"
                  />
                  <AppButton
                    text="Eliminar"
                    severity="danger"
                    size="xs"
                    variant="dropdown"
                    @click="deleteUser(user.id)"
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
