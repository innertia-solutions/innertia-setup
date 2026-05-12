<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const sessions = ref([])
const revokingId = ref(null)

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

async function revokeSession(id) {
  if (!confirm('¿Seguro que deseas revocar esta sesión?')) return
  revokingId.value = id
  try {
    await api.delete(`backoffice/sessions/${id}`)
    toast.success('Sesión revocada.')
    sessions.value = sessions.value.filter(s => s.id !== id)
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar la sesión.')
  } finally {
    revokingId.value = null
  }
}

async function revokeAll() {
  if (!confirm('¿Seguro que deseas revocar TODAS las sesiones activas?')) return
  try {
    await api.delete('backoffice/sessions')
    toast.success('Todas las sesiones han sido revocadas.')
    sessions.value = []
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar las sesiones.')
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
          @click="revokeAll"
        />
      </template>
    </AdminPageHeader>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div v-if="loading" class="px-5 py-16 flex items-center justify-center">
        <AppLoadingState label="Cargando sesiones..." />
      </div>

      <div v-else-if="!sessions.length" class="px-5 py-16">
        <AppEmptyState
          title="Sin sesiones activas"
          description="No hay sesiones activas en este momento."
        />
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dispositivo</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Último acceso</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="session in sessions"
              :key="session.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
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
              <td class="px-5 py-3">
                <AppButton
                  text="Revocar"
                  severity="danger"
                  size="xs"
                  variant="dropdown"
                  :loading="revokingId === session.id"
                  @click="revokeSession(session.id)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
