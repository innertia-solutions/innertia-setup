<template>
  <div>
    <!-- Stats grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4"
      >
        <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0" :class="stat.bgColor">
          <span class="w-6 h-6" :class="stat.iconColor" v-html="stat.icon" />
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          <p class="text-sm text-gray-500">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <!-- Recent activity -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-800">Actividad reciente</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="row in recentActivity" :key="row.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm text-gray-800 font-medium">{{ row.user }}</td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ row.action }}</td>
                <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ row.date }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent users -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-800">Usuarios recientes</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="u in recentUsers" :key="u.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-3 text-sm text-gray-800 font-medium">{{ u.name }}</td>
                <td class="px-5 py-3 text-sm text-gray-500">{{ u.email }}</td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                    {{ u.role }}
                  </span>
                </td>
                <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ u.created }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'backoffice',
  middleware: ['auth'],
})

const stats = [
  {
    label: 'Usuarios',
    value: 24,
    bgColor: 'bg-blue-50',
    iconColor: 'text-blue-600',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`,
  },
  {
    label: 'Sesiones activas',
    value: 8,
    bgColor: 'bg-green-50',
    iconColor: 'text-green-600',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
  },
  {
    label: 'Roles',
    value: 3,
    bgColor: 'bg-purple-50',
    iconColor: 'text-purple-600',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>`,
  },
  {
    label: 'Permisos',
    value: 12,
    bgColor: 'bg-amber-50',
    iconColor: 'text-amber-600',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>`,
  },
]

const recentActivity = [
  { id: 1, user: 'Ana García', action: 'Inicio de sesión', date: '10/05/2026 09:42' },
  { id: 2, user: 'Carlos López', action: 'Rol asignado: Supervisor', date: '10/05/2026 09:15' },
  { id: 3, user: 'María Torres', action: 'Contraseña restablecida', date: '09/05/2026 18:30' },
  { id: 4, user: 'Luis Ramírez', action: 'Inicio de sesión', date: '09/05/2026 17:05' },
  { id: 5, user: 'Admin', action: 'Permiso sincronizado: users.manage', date: '09/05/2026 16:00' },
]

const recentUsers = [
  { id: 1, name: 'Ana García', email: 'ana@empresa.com', role: 'Administrador', created: '01/05/2026' },
  { id: 2, name: 'Carlos López', email: 'carlos@empresa.com', role: 'Supervisor', created: '03/05/2026' },
  { id: 3, name: 'María Torres', email: 'maria@empresa.com', role: 'Operador', created: '05/05/2026' },
  { id: 4, name: 'Luis Ramírez', email: 'luis@empresa.com', role: 'Viewer', created: '07/05/2026' },
  { id: 5, name: 'Sofía Herrera', email: 'sofia@empresa.com', role: 'Operador', created: '09/05/2026' },
]
</script>
