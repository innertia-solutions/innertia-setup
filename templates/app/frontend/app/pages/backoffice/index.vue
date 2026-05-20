<script setup>
import {
  IconUsers, IconShieldCheck, IconDeviceDesktop, IconKey,
  IconTrendingUp, IconTrendingDown,
  IconCircleCheck, IconEdit, IconTrash, IconUpload,
  IconArrowRight,
} from '@tabler/icons-vue'

definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const authStore = useAuthStore()

const hour = new Date().getHours()
const greeting = hour < 12 ? 'Buenos días' : hour < 18 ? 'Buenas tardes' : 'Buenas noches'
const userName = computed(() => authStore.user?.name?.split(' ')[0] ?? 'Usuario')

const stats = [
  {
    label: 'Usuarios',
    value: '—',
    trend: '+0%',
    trendUp: true,
    sub: 'usuarios registrados',
    icon: IconUsers,
    iconBg: 'bg-blue-50 dark:bg-blue-500/10',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  {
    label: 'Roles',
    value: '—',
    trend: '+0',
    trendUp: true,
    sub: 'roles configurados',
    icon: IconShieldCheck,
    iconBg: 'bg-violet-50 dark:bg-violet-500/10',
    iconColor: 'text-violet-600 dark:text-violet-400',
  },
  {
    label: 'Permisos',
    value: '—',
    trend: '+0',
    trendUp: true,
    sub: 'permisos definidos',
    icon: IconKey,
    iconBg: 'bg-amber-50 dark:bg-amber-500/10',
    iconColor: 'text-amber-600 dark:text-amber-400',
  },
  {
    label: 'Sesiones activas',
    value: '—',
    trend: '+0',
    trendUp: true,
    sub: 'usuarios conectados',
    icon: IconDeviceDesktop,
    iconBg: 'bg-emerald-50 dark:bg-emerald-500/10',
    iconColor: 'text-emerald-600 dark:text-emerald-400',
  },
]
</script>

<template>
  <div class="p-2 sm:p-5 sm:py-0 md:pt-5 space-y-5">

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-foreground">{{ greeting }}, {{ userName }}</h1>
        <p class="mt-1 text-sm text-muted-foreground-1">Resumen general del sistema.</p>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
      <div v-for="stat in stats" :key="stat.label" class="bg-card border border-card-line shadow-2xs rounded-xl p-5">
        <div class="flex items-start justify-between gap-3 mb-4">
          <div class="flex-none size-10 rounded-lg flex items-center justify-center" :class="stat.iconBg">
            <component :is="stat.icon" :size="20" :class="stat.iconColor" />
          </div>
          <span class="inline-flex items-center gap-x-1 py-1 px-2 rounded-full text-xs font-semibold"
            :class="stat.trendUp ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
          >
            <IconTrendingUp v-if="stat.trendUp" :size="12" />
            <IconTrendingDown v-else :size="12" />
            {{ stat.trend }}
          </span>
        </div>
        <p class="text-3xl font-bold text-foreground">{{ stat.value }}</p>
        <p class="mt-1 text-sm font-medium text-muted-foreground-1">{{ stat.label }}</p>
        <p class="mt-1 text-xs text-muted-foreground-2">{{ stat.sub }}</p>
      </div>
    </div>

    <!-- Quick access + System status -->
    <div class="grid xl:grid-cols-2 gap-5 pb-10">

      <!-- Quick access -->
      <div class="bg-card border border-card-line shadow-2xs rounded-xl">
        <div class="px-5 py-4 border-b border-card-line">
          <h2 class="text-base font-semibold text-foreground">Acceso rápido</h2>
          <p class="text-sm text-muted-foreground-1">Acciones frecuentes del sistema</p>
        </div>
        <div class="p-5 space-y-2">
          <NuxtLink
            to="/backoffice/admin/users/new"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface hover:bg-muted-hover transition-colors"
          >
            <IconUsers class="size-4 text-blue-500" :stroke="1.5" />
            <span class="text-sm text-foreground">Crear nuevo usuario</span>
            <IconArrowRight class="size-4 text-muted-foreground ms-auto" :stroke="1.5" />
          </NuxtLink>
          <NuxtLink
            to="/backoffice/admin/roles/new"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface hover:bg-muted-hover transition-colors"
          >
            <IconShieldCheck class="size-4 text-violet-500" :stroke="1.5" />
            <span class="text-sm text-foreground">Crear nuevo rol</span>
            <IconArrowRight class="size-4 text-muted-foreground ms-auto" :stroke="1.5" />
          </NuxtLink>
          <NuxtLink
            to="/backoffice/admin/sessions"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-surface hover:bg-muted-hover transition-colors"
          >
            <IconDeviceDesktop class="size-4 text-emerald-500" :stroke="1.5" />
            <span class="text-sm text-foreground">Ver sesiones activas</span>
            <IconArrowRight class="size-4 text-muted-foreground ms-auto" :stroke="1.5" />
          </NuxtLink>
        </div>
      </div>

      <!-- System status -->
      <div class="bg-card border border-card-line shadow-2xs rounded-xl">
        <div class="px-5 py-4 border-b border-card-line">
          <h2 class="text-base font-semibold text-foreground">Estado del sistema</h2>
          <p class="text-sm text-muted-foreground-1">Estado actual de los servicios</p>
        </div>
        <div class="p-5 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">API</span>
            <App.Tag text="Operativo" severity="success" size="xs" />
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Base de datos</span>
            <App.Tag text="Operativo" severity="success" size="xs" />
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-muted-foreground">Autenticación</span>
            <App.Tag text="Operativo" severity="success" size="xs" />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
