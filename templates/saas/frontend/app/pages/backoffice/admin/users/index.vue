<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const users = ref([])

const deleteTarget = ref(null)
const deleteLoading = ref(false)

async function fetchUsers() {
  loading.value = true
  try {
    const data = await api.get('backoffice/users')
    users.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los usuarios.')
  } finally {
    loading.value = false
  }
}

async function confirmDelete() {
  deleteLoading.value = true
  try {
    await api.delete(`backoffice/users/${deleteTarget.value.id}`)
    toast.success('Usuario eliminado.')
    users.value = users.value.filter(u => u.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch {
    toast.error('Error al eliminar el usuario.')
  } finally {
    deleteLoading.value = false
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
          type="link"
          link="/backoffice/admin/users/new"
        />
      </template>
    </AdminPageHeader>

    <div class="bg-card border border-card-line rounded-xl overflow-hidden">
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState />
      </div>

      <div v-else-if="!users.length" class="px-5 py-16">
        <AppEmptyState
          title="Sin usuarios"
          description="Aún no hay usuarios registrados en el sistema."
        />
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-muted border-b border-card-line">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Email</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Roles</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Estado</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Creado</th>
              <th class="px-5 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-card-divider">
            <tr
              v-for="user in users"
              :key="user.id"
              class="hover:bg-layer-hover transition-colors"
            >
              <td class="px-5 py-3 text-sm font-medium text-foreground">{{ user.name }}</td>
              <td class="px-5 py-3 text-sm text-muted-foreground">{{ user.email }}</td>
              <td class="px-5 py-3">
                <div class="flex flex-wrap gap-1">
                  <AppTag
                    v-for="role in (user.roles ?? [])"
                    :key="role.id ?? role"
                    :text="role.name ?? role"
                    severity="secondary"
                    size="xs"
                  />
                  <span v-if="!user.roles?.length" class="text-xs text-muted-foreground-2">—</span>
                </div>
              </td>
              <td class="px-5 py-3">
                <AppTag
                  :text="user.active !== false ? 'Activo' : 'Inactivo'"
                  :severity="user.active !== false ? 'success' : 'secondary'"
                  size="xs"
                />
              </td>
              <td class="px-5 py-3 text-sm text-muted-foreground-2 whitespace-nowrap">{{ user.created_at ?? '—' }}</td>
              <td class="px-5 py-3 text-right">
                <AppDropdown
                  trigger-text="···"
                  placement="bottom-right"
                  :items="[
                    { label: 'Ver / Editar', type: 'button', action: () => navigateTo(`/backoffice/admin/users/${user.id}`) },
                    { label: 'Eliminar', type: 'button', severity: 'danger', action: () => deleteTarget = user },
                  ]"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ModalDeleteConfirm
      :model-value="!!deleteTarget"
      title="Eliminar usuario"
      :message="`¿Seguro que deseas eliminar a ${deleteTarget?.name}? Esta acción no se puede deshacer.`"
      :loading="deleteLoading"
      @update:model-value="deleteTarget = null"
      @confirm="confirmDelete"
    />
  </div>
</template>
