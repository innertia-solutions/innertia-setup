<template>
  <div>
    <!-- Page header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-gray-900">Sesiones activas</h1>
      <button
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-red-500 text-red-600 text-sm font-medium hover:bg-red-50 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
        Revocar todas
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">App</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inicio</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última actividad</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="session in sessions" :key="session.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-5 py-3">
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ session.user }}</p>
                  <p class="text-xs text-gray-400">{{ session.email }}</p>
                </div>
              </td>
              <td class="px-5 py-3 text-sm text-gray-500 font-mono">{{ session.ip }}</td>
              <td class="px-5 py-3 text-sm text-gray-500">{{ session.device }}</td>
              <td class="px-5 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                  {{ session.app }}
                </span>
              </td>
              <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ session.started }}</td>
              <td class="px-5 py-3 text-sm text-gray-400 whitespace-nowrap">{{ session.lastActivity }}</td>
              <td class="px-5 py-3">
                <button
                  class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors"
                >
                  Revocar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'backoffice',
  middleware: ['auth'],
})

const sessions = [
  {
    id: 1,
    user: 'Ana García',
    email: 'ana@empresa.com',
    ip: '192.168.1.10',
    device: 'Chrome / macOS',
    app: 'backoffice',
    started: '10/05/2026 08:00',
    lastActivity: '10/05/2026 09:45',
  },
  {
    id: 2,
    user: 'Carlos López',
    email: 'carlos@empresa.com',
    ip: '10.0.0.5',
    device: 'Firefox / Windows',
    app: 'backoffice',
    started: '10/05/2026 08:30',
    lastActivity: '10/05/2026 09:20',
  },
  {
    id: 3,
    user: 'María Torres',
    email: 'maria@empresa.com',
    ip: '172.16.0.3',
    device: 'Safari / iOS',
    app: 'mobile',
    started: '10/05/2026 07:15',
    lastActivity: '10/05/2026 09:10',
  },
  {
    id: 4,
    user: 'Luis Ramírez',
    email: 'luis@empresa.com',
    ip: '192.168.1.22',
    device: 'Chrome / Linux',
    app: 'backoffice',
    started: '09/05/2026 18:00',
    lastActivity: '09/05/2026 19:30',
  },
  {
    id: 5,
    user: 'Sofía Herrera',
    email: 'sofia@empresa.com',
    ip: '10.0.0.18',
    device: 'Edge / Windows',
    app: 'backoffice',
    started: '10/05/2026 09:00',
    lastActivity: '10/05/2026 09:40',
  },
  {
    id: 6,
    user: 'Diego Morales',
    email: 'diego@empresa.com',
    ip: '192.168.2.7',
    device: 'Chrome / Android',
    app: 'mobile',
    started: '10/05/2026 06:45',
    lastActivity: '10/05/2026 09:05',
  },
]
</script>
