<script setup>
import { IconUsers as IconUsersTab, IconShieldCheck, IconDeviceDesktop, IconTrash, IconPencil } from '@tabler/icons-vue'
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api      = useApi()
const toast    = useToast()
const tableRef = ref(null)

const adminTabs = [
  { label: 'Usuarios',  to: '/backoffice/admin/users',    icon: IconUsersTab    },
  { label: 'Roles',     to: '/backoffice/admin/roles',    icon: IconShieldCheck },
  { label: 'Sesiones',  to: '/backoffice/admin/sessions', icon: IconDeviceDesktop },
]

const columns = [
  { key: 'name',              label: 'Nombre',    sortable: true },
  { key: 'description',       label: 'Descripción' },
  { key: 'permissions_count', label: 'Permisos',  sortable: true, size: 100 },
  { key: 'users_count',       label: 'Usuarios',  sortable: true, size: 100 },
  { key: 'created_at',        label: 'Creado',    sortable: true },
  { key: 'actions',           label: '',          size: 88 },
]

// ── Permission groups (loaded once) ───────────────────────────────────────────
const permGroups = ref([])

onMounted(async () => {
  try {
    const data = await api.get('backoffice/permissions')
    permGroups.value = data?.data ?? data ?? []
  } catch {
    // non-critical — tree will show empty if unavailable
  }
})

// ── Delete ────────────────────────────────────────────────────────────────────
const showDeleteModal = ref(false)
const deletingRole    = ref(null)
const deleting        = ref(false)

const openDelete = (row) => {
  deletingRole.value = row
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!deletingRole.value) return
  deleting.value = true
  try {
    await api.delete(`backoffice/roles/${deletingRole.value.id}`)
    toast.success('Rol eliminado.')
    showDeleteModal.value = false
    tableRef.value?.closePreview()
    tableRef.value?.reload()
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al eliminar el rol.')
  } finally {
    deleting.value = false
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const rowPermissions = (row) => {
  const perms = row?.permissions
  if (!perms) return []
  if (typeof perms === 'string') {
    try { return JSON.parse(perms) } catch { return [] }
  }
  return Array.isArray(perms) ? perms : []
}

const rowPermissionNames = (row) => rowPermissions(row).map(p => p.name ?? p)
</script>

<template>
  <Admin.Page title="Roles" description="Gestión de roles y permisos." icon="IconShieldCheck" color="gray">
    <template #breadcrumb>
      <NuxtLink to="/backoffice" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Inicio</NuxtLink>
      <span class="text-xs text-slate-300 dark:text-slate-600">/</span>
      <span class="text-xs text-slate-500 dark:text-slate-400">Administración</span>
    </template>
    <template #actions>
      <App.Button type="link" link="/backoffice/admin/roles/new" text="Nuevo rol" severity="primary" size="sm" />
    </template>
    <template #tabs="{ color }">
      <Nav.Tabs :tabs="adminTabs" :color="color" />
    </template>

    <Table.Standard
      ref="tableRef"
      :table="Tables.roles"
      :columns="columns"
      search-placeholder="Buscar por nombre…"
      :cached="true"
    >
      <template #description="{ value }">
        <span class="text-sm text-slate-500 dark:text-slate-400">{{ value ?? '—' }}</span>
      </template>

      <template #permissions_count="{ value }">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
          {{ value ?? 0 }}
        </span>
      </template>

      <template #users_count="{ value }">
        <span class="text-sm text-slate-500 dark:text-slate-400">{{ value ?? 0 }}</span>
      </template>

      <template #created_at="{ value }">
        <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
          {{ value ? new Date(value).toLocaleDateString('es-CL') : '—' }}
        </span>
      </template>

      <template #actions="{ row }">
        <div class="flex items-center gap-0.5">
          <NuxtLink v-if="row?.id" :to="`/backoffice/admin/roles/${row.id}`"
            class="inline-flex items-center justify-center size-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            @click.stop
          >
            <IconPencil class="size-4" stroke="1.5" />
          </NuxtLink>
          <button type="button" @click.stop="openDelete(row)"
            class="inline-flex items-center justify-center size-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
          >
            <IconTrash class="size-4" stroke="1.5" />
          </button>
        </div>
      </template>

      <template #preview="{ row, close }">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-3">
            <div class="size-9 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0">
              <IconShieldCheck class="size-4 text-slate-600 dark:text-slate-400" />
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ row.name }}</p>
              <p class="text-xs text-slate-400 dark:text-slate-500 truncate">{{ row.description ?? 'Sin descripción' }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <button type="button" @click="openDelete(row)"
              class="inline-flex items-center justify-center size-7 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
            >
              <IconTrash class="size-4" stroke="1.5" />
            </button>
            <NuxtLink :to="`/backoffice/admin/roles/${row.id}`"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              Abrir
            </NuxtLink>
            <button type="button" @click="close"
              class="inline-flex items-center justify-center size-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
              <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-px bg-slate-200 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800">
          <div class="bg-card px-4 py-3 text-center">
            <p class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ row.permissions_count ?? 0 }}</p>
            <p class="text-xs text-slate-400">Permisos</p>
          </div>
          <div class="bg-card px-4 py-3 text-center">
            <p class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ row.users_count ?? 0 }}</p>
            <p class="text-xs text-slate-400">Usuarios</p>
          </div>
          <div class="bg-card px-4 py-3 text-center">
            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ row.created_at ? new Date(row.created_at).toLocaleDateString('es-CL') : '—' }}</p>
            <p class="text-xs text-slate-400">Creado</p>
          </div>
        </div>

        <!-- Permissions tree (readonly) -->
        <div class="overflow-y-auto flex-1 p-4">
          <PermissionsTree
            :groups="permGroups"
            :model-value="rowPermissionNames(row)"
            :readonly="true"
          />
        </div>
      </template>
    </Table.Standard>

    <Modal.DeleteConfirm
      v-model="showDeleteModal"
      :title="`Eliminar rol ${deletingRole?.name ?? ''}`"
      message="Esta acción eliminará el rol permanentemente. Los usuarios que lo tengan asignado perderán sus permisos."
      :loading="deleting"
      @confirm="confirmDelete"
    />
  </Admin.Page>
</template>
