<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const stats = [
  { label: 'Usuarios',         value: 24, icon: 'i-lucide-users',          severity: 'primary' },
  { label: 'Sesiones activas', value: 8,  icon: 'i-lucide-monitor',         severity: 'success' },
  { label: 'Roles',            value: 3,  icon: 'i-lucide-shield',           severity: 'info' },
  { label: 'Permisos',         value: 12, icon: 'i-lucide-key',              severity: 'warning' },
]

const severityBg = { primary: 'bg-blue-50 dark:bg-blue-900/20', success: 'bg-green-50 dark:bg-green-900/20', info: 'bg-cyan-50 dark:bg-cyan-900/20', warning: 'bg-yellow-50 dark:bg-yellow-900/20' }
const severityText = { primary: 'text-blue-600 dark:text-blue-400', success: 'text-green-600 dark:text-green-400', info: 'text-cyan-600 dark:text-cyan-400', warning: 'text-yellow-600 dark:text-yellow-400' }

const recentActivity = [
  { id: 1, user: 'Ana García',    action: 'Inicio de sesión',                date: '10/05/2026 09:42' },
  { id: 2, user: 'Carlos López',  action: 'Rol asignado: Supervisor',        date: '10/05/2026 09:15' },
  { id: 3, user: 'María Torres',  action: 'Contraseña restablecida',         date: '09/05/2026 18:30' },
  { id: 4, user: 'Luis Ramírez',  action: 'Inicio de sesión',                date: '09/05/2026 17:05' },
  { id: 5, user: 'Admin',         action: 'Permiso sincronizado: users.manage', date: '09/05/2026 16:00' },
]

const recentUsers = [
  { id: 1, name: 'Ana García',   email: 'ana@empresa.com',   role: 'Administrador', created: '01/05/2026' },
  { id: 2, name: 'Carlos López', email: 'carlos@empresa.com', role: 'Supervisor',   created: '03/05/2026' },
  { id: 3, name: 'María Torres', email: 'maria@empresa.com',  role: 'Operador',     created: '05/05/2026' },
  { id: 4, name: 'Luis Ramírez', email: 'luis@empresa.com',   role: 'Viewer',       created: '07/05/2026' },
  { id: 5, name: 'Sofía Herrera',email: 'sofia@empresa.com',  role: 'Operador',     created: '09/05/2026' },
]
</script>

<template>
  <div>
    <VantageAdminPageHeader title="Dashboard" />

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex items-center gap-4"
      >
        <div class="size-12 rounded-lg flex items-center justify-center shrink-0" :class="severityBg[stat.severity]">
          <Icon :name="stat.icon" class="size-5" :class="severityText[stat.severity]" />
        </div>
        <div>
          <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stat.value }}</p>
          <p class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Tables row -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent activity -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Actividad reciente</h2>
        </div>
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acción</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Fecha</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr v-for="row in recentActivity" :key="row.id">
              <td class="px-5 py-3 text-sm font-medium text-slate-800 dark:text-slate-200">{{ row.user }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ row.action }}</td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ row.date }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Recent users -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-200">Usuarios recientes</h2>
        </div>
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Rol</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Creado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr v-for="u in recentUsers" :key="u.id">
              <td class="px-5 py-3">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ u.name }}</p>
                <p class="text-xs text-slate-400">{{ u.email }}</p>
              </td>
              <td class="px-5 py-3">
                <VantageTag :text="u.role" severity="secondary" size="xs" />
              </td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ u.created }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
