<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const sessions = ref([])

const revokeTarget = ref(null)
const revokeLoading = ref(false)
const showRevokeAll = ref(false)
const revokeAllLoading = ref(false)

async function fetchSessions() {
  loading.value = true
  try {
    const data = await api.get('backoffice/sessions')
    sessions.value = data?.data ?? data ?? []
  } catch {
    toast.error('Error al cargar las sesiones.')
  } finally {
    loading.value = false
  }
}

async function confirmRevoke() {
  revokeLoading.value = true
  try {
    await api.delete(`backoffice/sessions/${revokeTarget.value.id}`)
    toast.success('Sesión revocada.')
    sessions.value = sessions.value.filter(s => s.id !== revokeTarget.value.id)
    revokeTarget.value = null
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar la sesión.')
  } finally {
    revokeLoading.value = false
  }
}

async function confirmRevokeAll() {
  revokeAllLoading.value = true
  try {
    await api.delete('backoffice/sessions')
    toast.success('Todas las sesiones han sido revocadas.')
    sessions.value = []
    showRevokeAll.value = false
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar las sesiones.')
  } finally {
    revokeAllLoading.value = false
  }
}

onMounted(fetchSessions)
</script>

<template>
  <div>
    <AdminPageHeader title="Sesiones activas" description="Sesiones de usuario actualmente activas en el sistema.">
      <template #actions>
        <AppButton
          v-if="sessions.length"
          text="Revocar todas"
          severity="danger"
          size="sm"
          @click="showRevokeAll = true"
        />
      </template>
    </AdminPageHeader>

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState />
      </div>

      <div v-else-if="!sessions.length" class="px-5 py-16">
        <AppEmptyState
          title="Sin sesiones activas"
          description="No hay sesiones activas en este momento."
        />
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">IP</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dispositivo</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Último acceso</th>
              <th class="px-5 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="session in sessions"
              :key="session.id"
              class="hover:bg-slate-50 dark:bg-slate-700/30 transition-colors"
            >
              <td class="px-5 py-3">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                  {{ session.user?.name ?? session.user_name ?? '—' }}
                </p>
                <p class="text-xs text-slate-400">
                  {{ session.user?.email ?? session.user_email ?? '' }}
                </p>
              </td>
              <td class="px-5 py-3 text-sm font-mono text-slate-500 dark:text-slate-400">
                {{ session.ip_address ?? session.ip ?? '—' }}
              </td>
              <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">
                {{ session.device ?? session.user_agent ?? '—' }}
              </td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">
                {{ session.last_activity_at ?? session.last_activity ?? '—' }}
              </td>
              <td class="px-5 py-3 text-right">
                <AppButton
                  text="Revocar"
                  severity="danger"
                  size="xs"
                  variant="dropdown"
                  @click="revokeTarget = session"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Revocar sesión individual -->
    <ModalDeleteConfirm
      :model-value="!!revokeTarget"
      title="Revocar sesión"
      :message="`¿Seguro que deseas revocar la sesión de ${revokeTarget?.user?.name ?? revokeTarget?.user_name ?? 'este usuario'}?`"
      confirm-text="Revocar"
      :loading="revokeLoading"
      @update:model-value="revokeTarget = null"
      @confirm="confirmRevoke"
    />

    <!-- Revocar todas -->
    <ModalDeleteConfirm
      v-model="showRevokeAll"
      title="Revocar todas las sesiones"
      message="¿Seguro que deseas revocar TODAS las sesiones activas? Todos los usuarios serán desconectados."
      confirm-text="Revocar todas"
      :loading="revokeAllLoading"
      @confirm="confirmRevokeAll"
    />
  </div>
</template>
