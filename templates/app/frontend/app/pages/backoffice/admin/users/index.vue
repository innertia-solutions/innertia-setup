<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const toast = useToast()
const { index: fetchUsersApi, destroy, reactivate, resetPassword } = useUsers()

const loading = ref(false)
const users = ref([])

// Search
const search = ref('')
let searchTimeout = null
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchUsers(), 300)
})

// Tabs
const activeTab = ref('active')
watch(activeTab, () => fetchUsers())

// Delete modal
const deleteTarget = ref(null)
const deleteLoading = ref(false)

// Reactivate modal
const reactivateTarget = ref(null)
const reactivateLoading = ref(false)

// Reset password modal
const resetTarget = ref(null)
const resetLoading = ref(false)

async function fetchUsers() {
  loading.value = true
  try {
    const data = await fetchUsersApi({
      search: search.value,
      with_roles: 1,
      trashed: activeTab.value === 'deleted' ? 1 : 0,
    })
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
    await destroy(deleteTarget.value.id)
    toast.success('Usuario eliminado.')
    users.value = users.value.filter(u => u.id !== deleteTarget.value.id)
    deleteTarget.value = null
  } catch {
    toast.error('Error al eliminar el usuario.')
  } finally {
    deleteLoading.value = false
  }
}

async function confirmReactivate() {
  reactivateLoading.value = true
  try {
    await reactivate(reactivateTarget.value.id)
    toast.success('Usuario reactivado. Se envió un email de activación.')
    users.value = users.value.filter(u => u.id !== reactivateTarget.value.id)
    reactivateTarget.value = null
  } catch {
    toast.error('Error al reactivar el usuario.')
  } finally {
    reactivateLoading.value = false
  }
}

async function confirmResetPassword() {
  resetLoading.value = true
  try {
    await resetPassword(resetTarget.value.id, { mode: 'email' })
    toast.success('Email de recuperación enviado.')
    resetTarget.value = null
  } catch {
    toast.error('Error al enviar el email de recuperación.')
  } finally {
    resetLoading.value = false
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

    <!-- Search + Tabs -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
      <div class="relative w-full sm:max-w-xs">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar usuarios..."
          class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 pl-9 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
        </svg>
      </div>

      <div class="flex gap-1 bg-slate-100 dark:bg-slate-700/50 rounded-lg p-1 w-fit">
        <button
          type="button"
          class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors"
          :class="activeTab === 'active'
            ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 shadow-sm'
            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
          @click="activeTab = 'active'"
        >
          Activos
        </button>
        <button
          type="button"
          class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors"
          :class="activeTab === 'deleted'
            ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 shadow-sm'
            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
          @click="activeTab = 'deleted'"
        >
          Eliminados
        </button>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState />
      </div>

      <div v-else-if="!users.length" class="px-5 py-16">
        <AppEmptyState
          :title="activeTab === 'deleted' ? 'Sin usuarios eliminados' : 'Sin usuarios'"
          :description="activeTab === 'deleted' ? 'No hay usuarios eliminados.' : 'Aún no hay usuarios registrados en el sistema.'"
        />
      </div>

      <!-- Tab: Activos -->
      <div v-else-if="activeTab === 'active'" class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Roles</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Creado</th>
              <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
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
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ user.created_at ?? '—' }}</td>
              <td class="px-5 py-3 text-right">
                <AppDropdown
                  trigger-text="···"
                  placement="bottom-right"
                  :items="[
                    { label: 'Ver detalle', type: 'button', action: () => navigateTo(`/backoffice/admin/users/${user.id}`) },
                    { label: 'Resetear contraseña', type: 'button', action: () => resetTarget = user },
                    { label: 'Eliminar', type: 'button', severity: 'danger', action: () => deleteTarget = user },
                  ]"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tab: Eliminados -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Eliminado</th>
              <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
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
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ user.deleted_at ?? '—' }}</td>
              <td class="px-5 py-3 text-right">
                <AppButton
                  text="Reactivar"
                  severity="secondary"
                  size="xs"
                  @click="reactivateTarget = user"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: Eliminar -->
    <ModalDeleteConfirm
      :model-value="!!deleteTarget"
      title="Eliminar usuario"
      :message="`¿Seguro que deseas eliminar a ${deleteTarget?.name}? Esta acción no se puede deshacer.`"
      :loading="deleteLoading"
      @update:model-value="deleteTarget = null"
      @confirm="confirmDelete"
    />

    <!-- Modal: Reactivar -->
    <Teleport to="body">
      <div
        v-if="reactivateTarget"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @click.self="reactivateTarget = null"
      >
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Reactivar usuario</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Se enviará un email de activación al usuario <span class="font-medium text-slate-700 dark:text-slate-300">{{ reactivateTarget.email }}</span>.
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton
              text="Cancelar"
              severity="secondary"
              size="sm"
              @click="reactivateTarget = null"
            />
            <AppButton
              text="Reactivar"
              severity="primary"
              size="sm"
              :loading="reactivateLoading"
              @click="confirmReactivate"
            />
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Resetear contraseña -->
    <Teleport to="body">
      <div
        v-if="resetTarget"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @click.self="resetTarget = null"
      >
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Resetear contraseña</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Se enviará un email de recuperación de contraseña a <span class="font-medium text-slate-700 dark:text-slate-300">{{ resetTarget.email }}</span>.
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton
              text="Cancelar"
              severity="secondary"
              size="sm"
              @click="resetTarget = null"
            />
            <AppButton
              text="Enviar email"
              severity="primary"
              size="sm"
              :loading="resetLoading"
              @click="confirmResetPassword"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
