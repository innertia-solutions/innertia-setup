<script setup>
definePageMeta({ layout: 'backoffice', middleware: ['auth'] })

const sessions = [
  { id: 1, user: 'Ana García',    email: 'ana@empresa.com',    ip: '192.168.1.10', device: 'Chrome / macOS',   app: 'backoffice', started: '10/05/2026 08:00', lastActivity: '10/05/2026 09:45' },
  { id: 2, user: 'Carlos López',  email: 'carlos@empresa.com', ip: '10.0.0.5',     device: 'Firefox / Windows', app: 'backoffice', started: '10/05/2026 08:30', lastActivity: '10/05/2026 09:20' },
  { id: 3, user: 'María Torres',  email: 'maria@empresa.com',  ip: '172.16.0.3',   device: 'Safari / iOS',     app: 'mobile',     started: '10/05/2026 07:15', lastActivity: '10/05/2026 09:10' },
  { id: 4, user: 'Luis Ramírez',  email: 'luis@empresa.com',   ip: '192.168.1.22', device: 'Chrome / Linux',   app: 'backoffice', started: '09/05/2026 18:00', lastActivity: '09/05/2026 19:30' },
  { id: 5, user: 'Sofía Herrera', email: 'sofia@empresa.com',  ip: '10.0.0.18',    device: 'Edge / Windows',   app: 'backoffice', started: '10/05/2026 09:00', lastActivity: '10/05/2026 09:40' },
  { id: 6, user: 'Diego Morales', email: 'diego@empresa.com',  ip: '192.168.2.7',  device: 'Chrome / Android', app: 'mobile',     started: '10/05/2026 06:45', lastActivity: '10/05/2026 09:05' },
]
</script>

<template>
  <div>
    <VantageAdminPageHeader title="Sesiones activas" description="Sesiones de usuario actualmente activas en el sistema.">
      <template #actions>
        <VantageButton text="Revocar todas" severity="danger" size="sm" />
      </template>
    </VantageAdminPageHeader>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-slate-50 dark:bg-slate-700/50">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">IP</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Dispositivo</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">App</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Inicio</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Última actividad</th>
              <th class="px-5 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr v-for="session in sessions" :key="session.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
              <td class="px-5 py-3">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ session.user }}</p>
                <p class="text-xs text-slate-400">{{ session.email }}</p>
              </td>
              <td class="px-5 py-3 text-sm font-mono text-slate-500 dark:text-slate-400">{{ session.ip }}</td>
              <td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">{{ session.device }}</td>
              <td class="px-5 py-3">
                <VantageTag :text="session.app" severity="secondary" size="xs" />
              </td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ session.started }}</td>
              <td class="px-5 py-3 text-sm text-slate-400 whitespace-nowrap">{{ session.lastActivity }}</td>
              <td class="px-5 py-3">
                <VantageButton text="Revocar" severity="danger" size="xs" variant="dropdown" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
