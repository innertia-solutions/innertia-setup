<script setup>
import { IconUsers, IconShieldCheck, IconDeviceDesktop, IconTrash, IconPencil, IconRefresh, IconExternalLink } from '@tabler/icons-vue'
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const api    = useApi()
const toast  = useToast()
const tableRef = ref(null)

const rtf = new Intl.RelativeTimeFormat('es', { numeric: 'auto' })
const timeAgo = (date) => {
  if (!date) return '—'
  const diff = (new Date(date).getTime() - Date.now()) / 1000
  for (const [unit, secs] of [['year',31536000],['month',2592000],['week',604800],['day',86400],['hour',3600],['minute',60],['second',1]]) {
    if (Math.abs(diff) >= secs || unit === 'second') return rtf.format(Math.round(diff / secs), unit)
  }
}

const adminTabs = [
  { label: 'Usuarios',  to: '/backoffice/admin/users',    icon: IconUsers         },
  { label: 'Roles',     to: '/backoffice/admin/roles',    icon: IconShieldCheck   },
  { label: 'Sesiones',  to: '/backoffice/admin/sessions', icon: IconDeviceDesktop },
]

const columns = [
  { key: 'name',               label: 'Nombre',        sortable: true },
  { key: 'email',              label: 'Email',         sortable: true },
  { key: 'roles',              label: 'Roles' },
  { key: 'status',             label: 'Estado',        sortable: true },
  { key: 'two_factor_enabled', label: '2FA',           sortable: true, size: 80 },
  { key: 'otp_configured',     label: 'OTP',           size: 80 },
  { key: 'seen_at',            label: 'Último acceso', sortable: true },
  { key: 'created_at',         label: 'Creado',        sortable: true },
  { key: 'actions',            label: '',              size: 100 },
]

const statusConfig = {
  active:           { label: 'Activo',              class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' },
  invited:          { label: 'Invitado',             class: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' },
  password_pending: { label: 'Contraseña pendiente', class: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' },
  unverified:       { label: 'Sin verificar',        class: 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300' },
  never_logged_in:  { label: 'Nunca ha ingresado',   class: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' },
  deleted:          { label: 'Eliminado',            class: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' },
}

const statusOptions = Object.entries(statusConfig).map(([value, { label }]) => ({ value, label }))
const defaultStatuses = statusOptions.filter(s => s.value !== 'deleted').map(s => s.value)
const selectedStatuses = ref([...defaultStatuses])

const STATUS_CACHE_KEY = 'table-statuses-users'
try {
  const cached = sessionStorage.getItem(STATUS_CACHE_KEY)
  if (cached) selectedStatuses.value = JSON.parse(cached)
} catch {}
watch(selectedStatuses, (v) => sessionStorage.setItem(STATUS_CACHE_KEY, JSON.stringify(v)), { deep: true })

const showStatusDropdown = ref(false)
const statusDropdownRef  = ref(null)

const toggleStatus = (value) => {
  const idx = selectedStatuses.value.indexOf(value)
  if (idx >= 0) selectedStatuses.value.splice(idx, 1)
  else selectedStatuses.value.push(value)
}

const onStatusOutsideClick = (e) => {
  if (statusDropdownRef.value && !statusDropdownRef.value.contains(e.target))
    showStatusDropdown.value = false
}
watch(showStatusDropdown, (v) => {
  if (v) document.addEventListener('mousedown', onStatusOutsideClick)
  else   document.removeEventListener('mousedown', onStatusOutsideClick)
})

const isStatusFiltered = computed(() => selectedStatuses.value.length !== statusOptions.length)
const resetStatuses = () => { selectedStatuses.value = [...defaultStatuses] }
const tableParams = computed(() => ({ statuses: selectedStatuses.value }))

// ─── Delete ──────────────────────────────────────────────────────────────────
const showDeleteModal = ref(false)
const deletingUser    = ref(null)
const deleting        = ref(false)

const openDelete = (row) => {
  deletingUser.value = row
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!deletingUser.value) return
  deleting.value = true
  try {
    await api.delete(`backoffice/users/${deletingUser.value.id}`)
    toast.success('Usuario eliminado.')
    showDeleteModal.value = false
    tableRef.value?.closePreview()
    tableRef.value?.reload()
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al eliminar el usuario.')
  } finally {
    deleting.value = false
  }
}

// ─── Reset password ───────────────────────────────────────────────────────────
const showResetModal = ref(false)
const resetTarget    = ref(null)
const resetting      = ref(false)

const openReset = (row) => {
  resetTarget.value = row
  showResetModal.value = true
}

const confirmReset = async () => {
  if (!resetTarget.value) return
  resetting.value = true
  try {
    await api.post(`backoffice/users/${resetTarget.value.id}/reset-password`, { mode: 'email' })
    toast.success('Email de recuperación enviado.')
    showResetModal.value = false
    resetTarget.value = null
  } catch (e) {
    toast.error(e?.data?.message ?? 'Error al enviar el email de recuperación.')
  } finally {
    resetting.value = false
  }
}
</script>

<template>
  <Admin.Page title="Usuarios" description="Gestión de usuarios del sistema." icon="IconUsers" color="gray">
    <template #breadcrumb>
      <NuxtLink to="/backoffice" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Inicio</NuxtLink>
      <span class="text-xs text-slate-300 dark:text-slate-600">/</span>
      <span class="text-xs text-slate-500 dark:text-slate-400">Administración</span>
    </template>
    <template #actions>
      <App.Button type="link" link="/backoffice/admin/users/new" text="Nuevo usuario" size="sm" />
    </template>
    <template #tabs="{ color }">
      <Nav.Tabs :tabs="adminTabs" :color="color" />
    </template>

    <Table.Standard
      ref="tableRef"
      name="users"
      endpoint="backoffice/users"
      :columns="columns"
      :params="tableParams"
      :show-filters="false"
      search-placeholder="Buscar por nombre o email…"
      :cached="true"
      :checkable="true"
    >
      <!-- Estado filter dropdown -->
      <template #toolbar>
        <div class="relative" ref="statusDropdownRef">
          <button
            type="button"
            @click="showStatusDropdown = !showStatusDropdown"
            :class="[
              'py-1.5 px-3 inline-flex items-center gap-2 text-sm font-medium rounded-lg border transition-colors',
              isStatusFiltered && selectedStatuses.length > 0
                ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-500 dark:text-indigo-300'
                : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700'
            ]"
          >
            Estado
            <span v-if="isStatusFiltered && selectedStatuses.length > 0" class="text-xs font-semibold">({{ selectedStatuses.length }})</span>
          </button>

          <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
          >
            <div
              v-if="showStatusDropdown"
              class="absolute top-full left-0 z-50 mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl py-1.5 min-w-52"
            >
              <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-3 pt-1.5 pb-2">Estado</p>
              <label
                v-for="opt in statusOptions"
                :key="opt.value"
                class="flex items-center gap-2.5 px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer"
              >
                <input
                  type="checkbox"
                  :checked="selectedStatuses.includes(opt.value)"
                  @change="toggleStatus(opt.value)"
                  class="rounded border-gray-300 dark:bg-slate-700 dark:border-slate-600 text-blue-600 focus:ring-0 focus:ring-offset-0"
                />
                <span class="text-sm text-slate-700 dark:text-slate-200">{{ opt.label }}</span>
              </label>
              <div class="border-t border-slate-100 dark:border-slate-700 mt-1.5 pt-1.5 px-2">
                <button
                  type="button"
                  @click="resetStatuses"
                  class="w-full text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 py-1 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                >Restablecer</button>
              </div>
            </div>
          </Transition>
        </div>
      </template>

      <!-- Roles -->
      <template #roles="{ value }">
        <div class="flex flex-wrap gap-1">
          <template v-if="Array.isArray(value) && value.length">
            <span
              v-for="(role, i) in value"
              :key="i"
              class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300"
            >{{ role?.name }}</span>
          </template>
          <span v-else class="text-xs text-slate-400">—</span>
        </div>
      </template>

      <!-- Estado -->
      <template #status="{ value }">
        <span v-if="statusConfig[value]"
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
          :class="statusConfig[value].class"
        >{{ statusConfig[value].label }}</span>
        <span v-else class="text-xs text-slate-400">—</span>
      </template>

      <!-- 2FA -->
      <template #two_factor_enabled="{ value }">
        <span v-if="value" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Activo</span>
        <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">—</span>
      </template>

      <!-- OTP -->
      <template #otp_configured="{ value }">
        <span v-if="value" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Sí</span>
        <span v-else class="text-xs text-slate-400">—</span>
      </template>

      <!-- Último acceso -->
      <template #seen_at="{ value }">
        <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap" :title="value ? new Date(value).toLocaleString('es-CL') : ''">
          {{ timeAgo(value) }}
        </span>
      </template>

      <!-- Fecha creación -->
      <template #created_at="{ value }">
        <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
          {{ value ? new Date(value).toLocaleDateString('es-CL') : '—' }}
        </span>
      </template>

      <!-- Acciones -->
      <template #actions="{ row }">
        <div class="flex items-center gap-0.5">
          <NuxtLink
            v-if="row?.id"
            :to="`/backoffice/admin/users/${row.id}`"
            class="inline-flex items-center justify-center size-8 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors"
            @click.stop
          >
            <IconPencil class="size-4" stroke="1.5" />
          </NuxtLink>
          <button
            type="button"
            @click.stop="openReset(row)"
            class="inline-flex items-center justify-center size-8 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
            title="Resetear contraseña"
          >
            <IconRefresh class="size-4" stroke="1.5" />
          </button>
          <button
            type="button"
            @click.stop="openDelete(row)"
            class="inline-flex items-center justify-center size-8 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
          >
            <IconTrash class="size-4" stroke="1.5" />
          </button>
        </div>
      </template>

      <!-- Preview panel header -->
      <template #preview-header="{ row, close }">
        <div class="flex items-center justify-between px-4 py-3">
          <div class="flex items-center gap-3">
            <div class="size-9 rounded-full bg-blue-600 text-white text-sm font-semibold flex items-center justify-center uppercase shrink-0">
              {{ row.name?.split(' ').slice(0,2).map(p => p[0]).join('') ?? '?' }}
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">{{ row.name }}</p>
              <p class="text-xs text-muted-foreground truncate">{{ row.email }}</p>
            </div>
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <button
              type="button"
              @click="openDelete(row)"
              class="inline-flex items-center justify-center size-7 rounded-lg text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
              title="Eliminar usuario"
            >
              <IconTrash class="size-4" stroke="1.5" />
            </button>
            <NuxtLink
              :to="`/backoffice/admin/users/${row.id}`"
              class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors"
            >
              Abrir
              <IconExternalLink class="size-3" stroke="1.5" />
            </NuxtLink>
            <button
              type="button"
              @click="close"
              class="inline-flex items-center justify-center size-7 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted-hover transition-colors"
            >
              <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>
      </template>

      <!-- Preview panel body -->
      <template #preview="{ row }">
        <div class="p-4 space-y-4">
          <!-- Estado + badges -->
          <div class="flex flex-wrap gap-2">
            <span v-if="statusConfig[row.status]"
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
              :class="statusConfig[row.status].class"
            >{{ statusConfig[row.status].label }}</span>
            <span v-if="row.two_factor_enabled" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">2FA activo</span>
            <span v-if="row.otp_configured" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">OTP configurado</span>
          </div>

          <!-- Roles -->
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1.5">Roles</p>
            <div class="flex flex-wrap gap-1.5">
              <template v-if="Array.isArray(row.roles) && row.roles.length">
                <span v-for="(role, i) in row.roles" :key="i"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300"
                >{{ role?.name }}</span>
              </template>
              <span v-else class="text-xs text-slate-400">Sin roles asignados</span>
            </div>
          </div>

          <!-- Datos -->
          <div class="space-y-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Información</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
              <div>
                <p class="text-xs text-slate-400 dark:text-slate-500">Último acceso</p>
                <p class="text-slate-700 dark:text-slate-300 font-medium">{{ timeAgo(row.seen_at) }}</p>
              </div>
              <div>
                <p class="text-xs text-slate-400 dark:text-slate-500">Creado</p>
                <p class="text-slate-700 dark:text-slate-300 font-medium">{{ row.created_at ? new Date(row.created_at).toLocaleDateString('es-CL') : '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </Table.Standard>

    <Modal.DeleteConfirm
      v-model="showDeleteModal"
      :title="`Eliminar a ${deletingUser?.name ?? 'usuario'}`"
      message="Esta acción eliminará al usuario permanentemente y no puede deshacerse."
      :loading="deleting"
      @confirm="confirmDelete"
    />

    <!-- Reset password modal -->
    <Modal.Base v-model="showResetModal" title="Resetear contraseña" size="sm">
      <p class="text-sm text-muted-foreground">
        Se enviará un email de recuperación a
        <span class="font-medium text-foreground">{{ resetTarget?.email }}</span>.
      </p>
      <template #footer>
        <App.Button text="Cancelar" severity="secondary" size="sm" @click="showResetModal = false" />
        <App.Button text="Enviar email" severity="primary" size="sm" :loading="resetting" @click="confirmReset" />
      </template>
    </Modal.Base>
  </Admin.Page>
</template>
