<script setup>
const { logout } = useAuth()
const authStore = useAuthStore()
const config = useRuntimeConfig()

const navLinks = [
  { to: '/backoffice',                         label: 'Dashboard', icon: 'i-lucide-layout-dashboard' },
  { to: '/backoffice/admin/users',             label: 'Usuarios',  icon: 'i-lucide-users' },
  { to: '/backoffice/admin/roles',             label: 'Roles',     icon: 'i-lucide-shield' },
  { to: '/backoffice/admin/permissions',       label: 'Permisos',  icon: 'i-lucide-key' },
  { to: '/backoffice/admin/sessions',          label: 'Sesiones',  icon: 'i-lucide-monitor' },
]

onMounted(() => {
  useUserRealtime().start()
})
</script>

<template>
  <LayoutAdmin>
    <template #logo>
      <NuxtLink to="/backoffice" class="px-3 text-base font-bold text-slate-900 dark:text-white truncate">
        {{ config.public.appName }}
      </NuxtLink>
    </template>

    <template #menu>
      <nav class="p-3 space-y-0.5">
        <NuxtLink
          v-for="link in navLinks"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors"
          active-class="bg-slate-100 dark:bg-slate-700 !text-slate-900 dark:!text-white font-medium"
        >
          <Icon :name="link.icon" class="size-4 shrink-0" />
          {{ link.label }}
        </NuxtLink>
      </nav>
    </template>

    <template #user-footer>
      <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
          <p class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">
            {{ authStore.user?.name ?? 'Usuario' }}
          </p>
          <p class="text-xs text-slate-400 truncate">{{ authStore.user?.email }}</p>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <NotificationBell />
          <button
            class="p-1.5 rounded-md text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
            @click="logout"
          >
            <Icon name="i-lucide-log-out" class="size-4" />
          </button>
        </div>
      </div>
    </template>
  </LayoutAdmin>
</template>
