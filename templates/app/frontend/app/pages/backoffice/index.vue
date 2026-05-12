<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const authStore = useAuthStore()

const stats = [
  { label: 'Usuarios',         value: '—', icon: 'i-lucide-users',          bg: 'bg-blue-50 dark:bg-blue-900/20',   text: 'text-blue-600 dark:text-blue-400' },
  { label: 'Roles',            value: '—', icon: 'i-lucide-shield',          bg: 'bg-violet-50 dark:bg-violet-900/20', text: 'text-violet-600 dark:text-violet-400' },
  { label: 'Permisos',         value: '—', icon: 'i-lucide-key',             bg: 'bg-amber-50 dark:bg-amber-900/20', text: 'text-amber-600 dark:text-amber-400' },
  { label: 'Sesiones activas', value: '—', icon: 'i-lucide-monitor',         bg: 'bg-green-50 dark:bg-green-900/20', text: 'text-green-600 dark:text-green-400' },
]

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Buenos días' : hour < 18 ? 'Buenas tardes' : 'Buenas noches'
const userName = computed(() => authStore.user?.name ?? 'Usuario')
</script>

<template>
  <div>
    <AdminPageHeader :title="`${greeting}, ${userName}`" description="Resumen general del sistema." />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4"
      >
        <div class="size-12 rounded-lg flex items-center justify-center shrink-0" :class="stat.bg">
          <Icon :name="stat.icon" class="size-5" :class="stat.text" />
        </div>
        <div>
          <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Quick access -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">Acceso rápido</h2>
        <div class="space-y-2">
          <NuxtLink
            to="/backoffice/admin/users/new"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
          >
            <Icon name="i-lucide-user-plus" class="size-4 text-blue-600 dark:text-blue-400" />
            <span class="text-sm text-slate-700 dark:text-slate-300">Crear nuevo usuario</span>
          </NuxtLink>
          <NuxtLink
            to="/backoffice/admin/roles/new"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
          >
            <Icon name="i-lucide-shield-plus" class="size-4 text-violet-600 dark:text-violet-400" />
            <span class="text-sm text-slate-700 dark:text-slate-300">Crear nuevo rol</span>
          </NuxtLink>
          <NuxtLink
            to="/backoffice/admin/sessions"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
          >
            <Icon name="i-lucide-monitor" class="size-4 text-green-600 dark:text-green-400" />
            <span class="text-sm text-slate-700 dark:text-slate-300">Ver sesiones activas</span>
          </NuxtLink>
        </div>
      </div>

      <!-- System info -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-4">Estado del sistema</h2>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500 dark:text-slate-400">API</span>
            <AppTag text="Operativo" severity="success" size="xs" />
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500 dark:text-slate-400">Base de datos</span>
            <AppTag text="Operativo" severity="success" size="xs" />
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-500 dark:text-slate-400">Autenticación</span>
            <AppTag text="Operativo" severity="success" size="xs" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
