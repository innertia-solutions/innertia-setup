<script setup>
import { IconUsers, IconShieldCheck, IconDeviceDesktop, IconTrash, IconBan } from '@tabler/icons-vue'
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api   = useApi()
const toast = useToast()
const tableRef = ref(null)

const adminTabs = [
  { label: 'Usuarios',  to: '/backoffice/admin/users',    icon: IconUsers         },
  { label: 'Roles',     to: '/backoffice/admin/roles',    icon: IconShieldCheck   },
  { label: 'Sesiones',  to: '/backoffice/admin/sessions', icon: IconDeviceDesktop },
]

const columns = [
  { key: 'user_name',        label: 'Usuario',       sortable: true },
  { key: 'ip_address',       label: 'IP' },
  { key: 'device',           label: 'Dispositivo' },
  { key: 'last_activity_at', label: 'Último acceso', sortable: true },
  { key: 'actions',          label: '',              size: 88 },
]

// ── Revoke single ─────────────────────────────────────────────────────────────
const showRevokeModal = ref(false)
const revokingSession = ref(null)
const revoking        = ref(false)

const openRevoke = (row) => {
  revokingSession.value = row
  showRevokeModal.value = true
}

const confirmRevoke = async () => {
  if (!revokingSession.value) return
  revoking.value = true
  try {
    await api.delete(`backoffice/sessions/${revokingSession.value.id}`)
    toast.success('Sesión revocada.')
    showRevokeModal.value = false
    tableRef.value?.closePreview()
    tableRef.value?.reload()
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar la sesión.')
  } finally {
    revoking.value = false
  }
}

// ── Revoke all ────────────────────────────────────────────────────────────────
const showRevokeAllModal = ref(false)
const revokingAll        = ref(false)

const confirmRevokeAll = async () => {
  revokingAll.value = true
  try {
    await api.delete('backoffice/sessions')
    toast.success('Todas las sesiones han sido revocadas.')
    showRevokeAllModal.value = false
    tableRef.value?.reload()
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al revocar las sesiones.')
  } finally {
    revokingAll.value = false
  }
}
</script>

<template>
  <Admin.Page title="Sesiones" description="Sesiones de usuario actualmente activas." icon="IconDeviceDesktop" color="gray">
    <template #breadcrumb>
      <NuxtLink to="/backoffice" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Inicio</NuxtLink>
      <span class="text-xs text-slate-300 dark:text-slate-600">/</span>
      <span class="text-xs text-slate-500 dark:text-slate-400">Administración</span>
    </template>
    <template #actions>
      <App.Button text="Revocar todas" severity="danger" size="sm" @click="showRevokeAllModal = true" />
    </template>
    <template #tabs="{ color }">
      <Nav.Tabs :tabs="adminTabs" :color="color" />
    </template>

    <Table.Standard
      ref="tableRef"
      name="sessions"
      endpoint="backoffice/sessions"
      :columns="columns"
      search-placeholder="Buscar por usuario o IP…"
      :cached="false"
    >
      <template #user_name="{ row }">
        <div>
          <p class="text-sm font-medium text-foreground">{{ row.user?.name ?? row.user_name ?? '—' }}</p>
          <p class="text-xs text-muted-foreground">{{ row.user?.email ?? row.user_email ?? '' }}</p>
        </div>
      </template>

      <template #ip_address="{ value }">
        <span class="text-sm font-mono text-muted-foreground">{{ value ?? '—' }}</span>
      </template>

      <template #device="{ row }">
        <span class="text-sm text-muted-foreground">{{ row.device ?? row.user_agent ?? '—' }}</span>
      </template>

      <template #last_activity_at="{ row }">
        <span class="text-sm text-muted-foreground whitespace-nowrap">
          {{ row.last_activity_at ?? row.last_activity ?? '—' }}
        </span>
      </template>

      <template #actions="{ row }">
        <button type="button" @click.stop="openRevoke(row)"
          class="inline-flex items-center justify-center size-8 rounded-lg text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
          title="Revocar sesión"
        >
          <IconBan class="size-4" stroke="1.5" />
        </button>
      </template>
    </Table.Standard>

    <Modal.DeleteConfirm
      v-model="showRevokeModal"
      title="Revocar sesión"
      :message="`¿Revocar la sesión de ${revokingSession?.user?.name ?? revokingSession?.user_name ?? 'este usuario'}?`"
      confirm-text="Revocar"
      :loading="revoking"
      @confirm="confirmRevoke"
    />

    <Modal.DeleteConfirm
      v-model="showRevokeAllModal"
      title="Revocar todas las sesiones"
      message="Todos los usuarios serán desconectados inmediatamente."
      confirm-text="Revocar todas"
      :loading="revokingAll"
      @confirm="confirmRevokeAll"
    />
  </Admin.Page>
</template>
