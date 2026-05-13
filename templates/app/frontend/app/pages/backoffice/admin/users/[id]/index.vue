<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const route = useRoute()
const toast = useToast()
const {
  show,
  update,
  resetPassword,
  getRoles,
  assignRole,
  removeRole,
  getSessions,
  revokeSession,
  revokeAllSessions,
  getActivityLog,
} = useUsers()
const { index: fetchRolesApi } = useRoles()

const id = computed(() => route.params.id)

// Active tab
const activeTab = ref('info')

// ─── Info tab ───────────────────────────────────────────────────────────────
const loadingUser = ref(false)
const saving = ref(false)
const user = ref(null)

const form = reactive({ name: '', email: '' })
const errors = reactive({ name: '', email: '' })

function clearErrors() {
  Object.keys(errors).forEach(k => errors[k] = '')
}

async function fetchUser() {
  loadingUser.value = true
  try {
    const data = await show(id.value)
    user.value = data?.data ?? data
    form.name = user.value.name ?? ''
    form.email = user.value.email ?? ''
  } catch {
    toast.error('Error al cargar el usuario.')
  } finally {
    loadingUser.value = false
  }
}

async function handleSave() {
  clearErrors()
  saving.value = true
  try {
    await update(id.value, { name: form.name, email: form.email })
    toast.success('Usuario actualizado correctamente.')
  } catch (e) {
    const errs = e?.data?.errors ?? {}
    Object.keys(errs).forEach(k => { if (k in errors) errors[k] = errs[k][0] ?? '' })
    toast.error(e?.data?.message ?? 'Error al actualizar el usuario.')
  } finally {
    saving.value = false
  }
}

// Reset password modal (info tab)
const showResetModal = ref(false)
const resetLoading = ref(false)

async function confirmResetPassword() {
  resetLoading.value = true
  try {
    await resetPassword(id.value, { mode: 'email' })
    toast.success('Email de recuperación enviado.')
    showResetModal.value = false
  } catch {
    toast.error('Error al enviar el email de recuperación.')
  } finally {
    resetLoading.value = false
  }
}

// ─── Roles tab ───────────────────────────────────────────────────────────────
const loadingUserRoles = ref(false)
const userRoles = ref([])
const allRoles = ref([])
const loadingAllRoles = ref(false)
const selectedRoleToAdd = ref('')
const addingRole = ref(false)
const removingRole = ref(null)

async function fetchUserRoles() {
  loadingUserRoles.value = true
  try {
    const data = await getRoles(id.value)
    userRoles.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los roles del usuario.')
  } finally {
    loadingUserRoles.value = false
  }
}

async function fetchAllRoles() {
  loadingAllRoles.value = true
  try {
    const data = await fetchRolesApi()
    allRoles.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar los roles disponibles.')
  } finally {
    loadingAllRoles.value = false
  }
}

async function handleAddRole() {
  if (!selectedRoleToAdd.value) return
  addingRole.value = true
  try {
    await assignRole(id.value, selectedRoleToAdd.value)
    toast.success('Rol asignado.')
    selectedRoleToAdd.value = ''
    await fetchUserRoles()
  } catch {
    toast.error('Error al asignar el rol.')
  } finally {
    addingRole.value = false
  }
}

async function handleRemoveRole(roleName) {
  removingRole.value = roleName
  try {
    await removeRole(id.value, roleName)
    toast.success('Rol eliminado.')
    await fetchUserRoles()
  } catch {
    toast.error('Error al eliminar el rol.')
  } finally {
    removingRole.value = null
  }
}

const availableRolesToAdd = computed(() => {
  const currentNames = userRoles.value.map(r => r.name ?? r)
  return allRoles.value.filter(r => !currentNames.includes(r.name ?? r))
})

// ─── Sessions tab ────────────────────────────────────────────────────────────
const loadingSessions = ref(false)
const sessions = ref([])
const revokeTarget = ref(null)
const revokeLoading = ref(false)
const revokeAllLoading = ref(false)
const showRevokeAllConfirm = ref(false)

async function fetchSessions() {
  loadingSessions.value = true
  try {
    const data = await getSessions(id.value)
    sessions.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar las sesiones.')
  } finally {
    loadingSessions.value = false
  }
}

async function handleRevokeSession() {
  revokeLoading.value = true
  try {
    await revokeSession(id.value, revokeTarget.value.id)
    toast.success('Sesión revocada.')
    revokeTarget.value = null
    await fetchSessions()
  } catch {
    toast.error('Error al revocar la sesión.')
  } finally {
    revokeLoading.value = false
  }
}

async function handleRevokeAll() {
  revokeAllLoading.value = true
  try {
    await revokeAllSessions(id.value)
    toast.success('Todas las sesiones han sido revocadas.')
    showRevokeAllConfirm.value = false
    await fetchSessions()
  } catch {
    toast.error('Error al revocar las sesiones.')
  } finally {
    revokeAllLoading.value = false
  }
}

// ─── Activity tab ────────────────────────────────────────────────────────────
const loadingActivity = ref(false)
const loadingMoreActivity = ref(false)
const activityLog = ref([])
const activityPage = ref(1)
const activityHasMore = ref(true)

async function fetchActivity(page = 1, append = false) {
  if (page === 1) loadingActivity.value = true
  else loadingMoreActivity.value = true
  try {
    const data = await getActivityLog(id.value, { page })
    const items = data?.data ?? data ?? []
    if (append) {
      activityLog.value = [...activityLog.value, ...items]
    } else {
      activityLog.value = items
    }
    activityHasMore.value = items.length > 0
  } catch {
    toast.error('Error al cargar el log de actividad.')
  } finally {
    loadingActivity.value = false
    loadingMoreActivity.value = false
  }
}

async function loadMoreActivity() {
  activityPage.value++
  await fetchActivity(activityPage.value, true)
}

// ─── Tab switching ────────────────────────────────────────────────────────────
const rolesTabLoaded = ref(false)
const sessionsTabLoaded = ref(false)
const activityTabLoaded = ref(false)

watch(activeTab, async (tab) => {
  if (tab === 'roles' && !rolesTabLoaded.value) {
    rolesTabLoaded.value = true
    await Promise.all([fetchUserRoles(), fetchAllRoles()])
  }
  if (tab === 'sessions' && !sessionsTabLoaded.value) {
    sessionsTabLoaded.value = true
    await fetchSessions()
  }
  if (tab === 'activity' && !activityTabLoaded.value) {
    activityTabLoaded.value = true
    await fetchActivity(1)
  }
})

onMounted(fetchUser)
</script>

<template>
  <div>
    <AdminPageHeader
      :title="loadingUser ? 'Cargando...' : (user?.name ?? 'Usuario')"
      description="Ver y editar información del usuario."
    >
      <template #actions>
        <AppTag
          v-if="user?.deleted_at"
          text="Eliminado"
          severity="danger"
          size="sm"
        />
        <AppButton
          text="Volver"
          severity="secondary"
          size="sm"
          type="link"
          link="/backoffice/admin/users"
        />
      </template>
    </AdminPageHeader>

    <div v-if="loadingUser" class="flex items-center justify-center py-20">
      <AppLoadingState />
    </div>

    <div v-else-if="!user" class="py-16">
      <AppEmptyState
        title="Usuario no encontrado"
        description="El usuario solicitado no existe o no tienes permisos para verlo."
      />
    </div>

    <div v-else>
      <!-- Tab nav -->
      <div class="flex gap-1 border-b border-slate-200 dark:border-slate-700 mb-6">
        <button
          v-for="tab in [
            { key: 'info', label: 'Información' },
            { key: 'roles', label: 'Roles' },
            { key: 'sessions', label: 'Sesiones activas' },
            { key: 'activity', label: 'Actividad' },
          ]"
          :key="tab.key"
          type="button"
          class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
          :class="activeTab === tab.key
            ? 'border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400'
            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- ── Tab: Información ── -->
      <div v-show="activeTab === 'info'" class="max-w-xl space-y-6">
        <!-- Form -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-5">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Información general</h2>

          <FormsInput
            v-model="form.name"
            label="Nombre completo"
            placeholder="Nombre del usuario"
            :error="errors.name || null"
          />

          <FormsInput
            v-model="form.email"
            type="email"
            label="Correo electrónico"
            placeholder="usuario@empresa.com"
            :error="errors.email || null"
          />

          <div class="pt-2 flex items-center justify-between">
            <AppButton
              text="Resetear contraseña"
              severity="secondary"
              size="sm"
              @click="showResetModal = true"
            />
            <AppButton
              text="Guardar cambios"
              severity="primary"
              size="md"
              :loading="saving"
              @click="handleSave"
            />
          </div>
        </div>

        <!-- Metadata -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">Información del sistema</h2>
          <dl class="space-y-3">
            <div class="flex items-center justify-between">
              <dt class="text-sm text-slate-500 dark:text-slate-400">ID</dt>
              <dd class="text-sm font-mono text-slate-800 dark:text-slate-200">{{ user.id }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-slate-500 dark:text-slate-400">Estado</dt>
              <dd>
                <AppTag
                  :text="user.deleted_at ? 'Eliminado' : (user.active !== false ? 'Activo' : 'Inactivo')"
                  :severity="user.deleted_at ? 'danger' : (user.active !== false ? 'success' : 'secondary')"
                  size="xs"
                />
              </dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-slate-500 dark:text-slate-400">Creado</dt>
              <dd class="text-sm text-slate-800 dark:text-slate-200">{{ user.created_at ?? '—' }}</dd>
            </div>
            <div class="flex items-center justify-between">
              <dt class="text-sm text-slate-500 dark:text-slate-400">Último acceso</dt>
              <dd class="text-sm text-slate-800 dark:text-slate-200">{{ user.last_login_at ?? '—' }}</dd>
            </div>
            <div v-if="user.deleted_at" class="flex items-center justify-between">
              <dt class="text-sm text-slate-500 dark:text-slate-400">Eliminado</dt>
              <dd class="text-sm text-red-500">{{ user.deleted_at }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- ── Tab: Roles ── -->
      <div v-show="activeTab === 'roles'" class="max-w-xl space-y-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-5">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Roles asignados</h2>

          <div v-if="loadingUserRoles" class="flex items-center justify-center py-8">
            <AppLoadingState />
          </div>

          <div v-else-if="!userRoles.length" class="py-4">
            <AppEmptyState
              title="Sin roles"
              description="Este usuario no tiene roles asignados."
            />
          </div>

          <div v-else class="flex flex-wrap gap-2">
            <div
              v-for="role in userRoles"
              :key="role.id ?? role.name"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-700"
            >
              {{ role.name ?? role }}
              <button
                type="button"
                class="hover:text-red-500 transition-colors disabled:opacity-50"
                :disabled="removingRole === (role.name ?? role)"
                @click="handleRemoveRole(role.name ?? role)"
              >
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Add role -->
          <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
            <label class="block text-sm font-medium text-slate-800 dark:text-slate-200 mb-2">Agregar rol</label>
            <div class="flex gap-2">
              <select
                v-model="selectedRoleToAdd"
                class="flex-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 px-3 py-2 text-sm text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Seleccionar rol...</option>
                <option
                  v-for="role in availableRolesToAdd"
                  :key="role.id ?? role.name"
                  :value="role.name ?? role"
                >
                  {{ role.name ?? role }}
                </option>
              </select>
              <AppButton
                text="Agregar"
                severity="primary"
                size="sm"
                :loading="addingRole"
                :disabled="!selectedRoleToAdd"
                @click="handleAddRole"
              />
            </div>
            <p v-if="loadingAllRoles" class="mt-1 text-xs text-slate-400">Cargando roles disponibles...</p>
          </div>
        </div>
      </div>

      <!-- ── Tab: Sesiones activas ── -->
      <div v-show="activeTab === 'sessions'" class="space-y-4">
        <div class="flex justify-end">
          <AppButton
            v-if="sessions.length"
            text="Revocar todas"
            severity="danger"
            size="sm"
            @click="showRevokeAllConfirm = true"
          />
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
          <div v-if="loadingSessions" class="px-5 py-16 flex items-center justify-center">
            <AppLoadingState />
          </div>

          <div v-else-if="!sessions.length" class="px-5 py-16">
            <AppEmptyState
              title="Sin sesiones activas"
              description="El usuario no tiene sesiones activas en este momento."
            />
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP</th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Agente / Dispositivo</th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Creada</th>
                  <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Última actividad</th>
                  <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <tr
                  v-for="session in sessions"
                  :key="session.id"
                  class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                >
                  <td class="px-5 py-3 text-sm font-mono text-slate-500 dark:text-slate-400">{{ session.ip_address ?? '—' }}</td>
                  <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ session.user_agent ?? '—' }}</td>
                  <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ session.created_at ?? '—' }}</td>
                  <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ session.last_activity_at ?? session.last_activity ?? '—' }}</td>
                  <td class="px-5 py-3 text-right">
                    <AppButton
                      text="Revocar"
                      severity="danger"
                      size="xs"
                      @click="revokeTarget = session"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── Tab: Actividad ── -->
      <div v-show="activeTab === 'activity'" class="max-w-2xl space-y-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
          <div v-if="loadingActivity" class="px-5 py-16 flex items-center justify-center">
            <AppLoadingState />
          </div>

          <div v-else-if="!activityLog.length" class="px-5 py-16">
            <AppEmptyState
              title="Sin actividad"
              description="No hay eventos registrados para este usuario."
            />
          </div>

          <ul v-else class="divide-y divide-slate-100 dark:divide-slate-700">
            <li
              v-for="(entry, i) in activityLog"
              :key="entry.id ?? i"
              class="px-5 py-3 flex items-start justify-between gap-4"
            >
              <span class="text-sm text-slate-700 dark:text-slate-300">{{ entry.description ?? entry.event ?? entry.action ?? '—' }}</span>
              <span class="text-xs text-slate-400 whitespace-nowrap shrink-0">{{ entry.created_at ?? entry.date ?? '—' }}</span>
            </li>
          </ul>

          <div v-if="activityLog.length && activityHasMore" class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 flex justify-center">
            <AppButton
              text="Cargar más"
              severity="secondary"
              size="sm"
              :loading="loadingMoreActivity"
              @click="loadMoreActivity"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Resetear contraseña (info tab) -->
    <Teleport to="body">
      <div
        v-if="showResetModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @click.self="showResetModal = false"
      >
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Resetear contraseña</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Se enviará un email de recuperación de contraseña a <span class="font-medium text-slate-700 dark:text-slate-300">{{ user?.email }}</span>.
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton
              text="Cancelar"
              severity="secondary"
              size="sm"
              @click="showResetModal = false"
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

    <!-- Modal: Revocar sesión individual -->
    <Teleport to="body">
      <div
        v-if="revokeTarget"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @click.self="revokeTarget = null"
      >
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Revocar sesión</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            ¿Confirmas que deseas revocar esta sesión? El usuario perderá acceso inmediatamente.
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton
              text="Cancelar"
              severity="secondary"
              size="sm"
              @click="revokeTarget = null"
            />
            <AppButton
              text="Revocar"
              severity="danger"
              size="sm"
              :loading="revokeLoading"
              @click="handleRevokeSession"
            />
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal: Revocar todas las sesiones -->
    <Teleport to="body">
      <div
        v-if="showRevokeAllConfirm"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
        @click.self="showRevokeAllConfirm = false"
      >
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl w-full max-w-sm p-6 space-y-4">
          <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Revocar todas las sesiones</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            ¿Confirmas que deseas revocar todas las sesiones activas de este usuario?
          </p>
          <div class="flex justify-end gap-2 pt-2">
            <AppButton
              text="Cancelar"
              severity="secondary"
              size="sm"
              @click="showRevokeAllConfirm = false"
            />
            <AppButton
              text="Revocar todas"
              severity="danger"
              size="sm"
              :loading="revokeAllLoading"
              @click="handleRevokeAll"
            />
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
